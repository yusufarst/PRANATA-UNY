<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Models\User;
use App\Modules\Identity\Actions\ApproveAssistedReset;
use App\Modules\Identity\Actions\CreateAccount;
use App\Modules\Identity\Actions\DeactivateAccount;
use App\Modules\Identity\Actions\RequestAssistedReset;
use App\Modules\Identity\Actions\RevokeAssistedReset;
use App\Modules\Identity\Actions\RevokeSessions;
use App\Modules\Identity\Actions\UpdateAccount;
use App\Modules\Identity\Events\SessionsRevoked;
use App\Modules\Identity\Http\Requests\AccountRequest;
use App\Modules\Identity\Http\Requests\AssistedResetRequest;
use App\Modules\Identity\Http\Requests\DeactivateAccountRequest;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Identity\Providers\IdentityServiceProvider;
use App\Modules\Identity\Support\ActiveSessions;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PG-502 Pengguna (INFORMATION_ARCHITECTURE section 3.6): the list, detail and form of accounts, and their deactivation.
 *
 * Every route also carries the middleware can:admin.users.manage; the controller checks the same capability again.
 */
class UserAdministrationController
{
    private const STATUSES = [User::ACTIVE, User::SUSPENDED, User::LOCKED, User::DEACTIVATED];

    private const ROLE_GROUPS = [User::SUPER_ADMIN, User::INTERNAL, User::VENDOR];

    public function __construct(private readonly PrivilegedAccounts $privileged) {}

    public function index(Request $request): Response
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        $filters = [
            'q' => trim($request->string('q')->value()),
            'status' => in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : '',
            'role_group' => in_array($request->query('role_group'), self::ROLE_GROUPS, true) ? $request->query('role_group') : '',
        ];

        $users = User::query()
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%'.addcslashes(mb_strtolower($filters['q']), '\\%_').'%';

                $query->where(fn ($query) => $query
                    ->whereRaw('lower(username) like ?', [$term])
                    ->orWhereRaw('lower(name) like ?', [$term])
                    ->orWhereRaw('lower(email) like ?', [$term]));
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['role_group'] !== '', fn ($query) => $query->where('role_group', $filters['role_group']))
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (User $user): array => self::summary($user));

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        return Inertia::render('admin/users/form', ['account' => null]);
    }

    /**
     * The activation code is in this answer only: it is not flashed to the session, and a resend of the command gets a
     * redirect without it (TASK-011 TAC-01).
     */
    public function store(AccountRequest $request, CreateAccount $createAccount): Response
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        $created = $createAccount->handle($request->user(), [
            'role_group' => $request->string('role_group')->value(),
            ...$request->details(),
        ]);

        return Inertia::render('admin/users/show', [
            ...$this->detail($request, $created['user']->refresh()),
            'issuedSecret' => [
                'kind' => 'activation',
                'value' => $created['code'],
                'expires_at' => $created['expires_at']->toIso8601String(),
            ],
        ]);
    }

    public function show(Request $request, User $user): Response
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        return Inertia::render('admin/users/show', $this->detail($request, $user));
    }

    public function edit(User $user): Response|RedirectResponse
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        if ($user->status === User::DEACTIVATED) {
            return to_route('identity.admin.users.show', $user->public_id);
        }

        return Inertia::render('admin/users/form', ['account' => self::account($user)]);
    }

    public function update(AccountRequest $request, User $user, UpdateAccount $updateAccount): RedirectResponse
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        try {
            $updateAccount->handle($user, $request->details(), $request->integer('lock_version'));
        } catch (StaleVersionException $exception) {
            throw $exception->showing('admin/users/form', ['account' => self::account($user->fresh() ?? $user)]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('identity.account_saved')]);

        return to_route('identity.admin.users.show', $user->public_id);
    }

    public function deactivate(DeactivateAccountRequest $request, User $user, DeactivateAccount $deactivateAccount): RedirectResponse
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        try {
            $deactivateAccount->handle($request->user(), $user, $request->string('reason')->trim()->value(), $request->integer('lock_version'));
        } catch (StaleVersionException $exception) {
            throw $exception->showing('admin/users/show', $this->detail($request, $user->fresh() ?? $user));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('identity.account_deactivated_done')]);

        return to_route('identity.admin.users.show', $user->public_id);
    }

    /**
     * Like the activation code, the temporary password is in this answer only (TASK-011 TAC-02). A privileged account gets a
     * request for a second administrator instead (TAC-03).
     */
    public function requestReset(AssistedResetRequest $request, User $user, RequestAssistedReset $requestReset): Response|RedirectResponse
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        $result = $requestReset->handle(
            $request->user(),
            $user,
            $request->string('identity_check_method')->value(),
            $request->note(),
        );

        if ($result['password'] === null) {
            Inertia::flash('toast', ['type' => 'success', 'message' => __('identity.reset_awaits_approval')]);

            return to_route('identity.admin.users.show', $user->public_id);
        }

        return $this->showingTemporaryPassword($request, $user, $result['password'], $result['expires_at']);
    }

    public function approveReset(Request $request, User $user, PasswordReset $reset, ApproveAssistedReset $approveReset): Response
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);
        abort_unless($reset->user_id === $user->id, 404);

        $issued = $approveReset->handle($request->user(), $reset);

        return $this->showingTemporaryPassword($request, $user, $issued['password'], $issued['expires_at']);
    }

    public function revokeReset(User $user, PasswordReset $reset, RevokeAssistedReset $revokeReset): RedirectResponse
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);
        abort_unless($reset->user_id === $user->id, 404);

        $revokeReset->handle($reset);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('identity.reset_revoked')]);

        return to_route('identity.admin.users.show', $user->public_id);
    }

    /**
     * Ends every session of another account (TASK-011 TAC-04); an administrator ends their own on their sessions page.
     */
    public function revokeSessions(Request $request, User $user, RevokeSessions $revokeSessions): RedirectResponse
    {
        Gate::authorize(IdentityServiceProvider::MANAGE_USERS);

        if ($user->is($request->user())) {
            throw DomainRuleViolation::because('identity.cannot_revoke_own_sessions_here');
        }

        $ended = $revokeSessions->handle($user, SessionsRevoked::ALL);

        Inertia::flash('toast', ['type' => 'success', 'message' => trans_choice('identity.sessions_revoked', $ended, ['count' => $ended])]);

        return to_route('identity.admin.users.show', $user->public_id);
    }

    private function showingTemporaryPassword(Request $request, User $user, string $password, CarbonInterface $expiresAt): Response
    {
        return Inertia::render('admin/users/show', [
            ...$this->detail($request, $user->refresh()),
            'issuedSecret' => [
                'kind' => 'temporary_password',
                'value' => $password,
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(Request $request, User $user): array
    {
        $deactivated = $user->status === User::DEACTIVATED;
        $own = $user->is($request->user());

        $pending = PasswordReset::query()
            ->with('requester')
            ->where('user_id', $user->getKey())
            ->where('status', PasswordReset::PENDING_APPROVAL)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        $sessions = ActiveSessions::of($user, $own ? $request->session()->getId() : null);

        return [
            'account' => [
                ...self::account($user),
                'last_login_at' => $user->last_login_at?->toIso8601String(),
                'deactivated_at' => $user->deactivated_at?->toIso8601String(),
                'created_at' => $user->created_at?->toIso8601String(),
                'privileged' => $this->privileged->isPrivileged($user),
            ],
            'pendingReset' => $pending === null ? null : [
                'id' => $pending->id,
                'requested_by' => $pending->requester?->name,
                'requested_by_me' => $pending->requested_by === $request->user()?->getKey(),
                'identity_check_method' => $pending->identity_check_method,
                'identity_check_note' => $pending->identity_check_note,
                'created_at' => $pending->created_at?->toIso8601String(),
                'expires_at' => $pending->expires_at->toIso8601String(),
            ],
            'sessions' => $sessions,
            'can' => [
                'update' => ! $deactivated,
                'deactivate' => ! $deactivated && ! $own,
                // An administrator never resets their own account through the assisted path (TASK-011).
                'reset' => ! $deactivated && ! $own && $pending === null,
                // The second administrator is neither the requester nor the account (TASK-011 TAC-03).
                'approveReset' => $pending !== null && $pending->requested_by !== $request->user()?->getKey() && ! $own,
                'revokeReset' => $pending !== null && ! $own,
                // The own sessions are ended on the own sessions page, where the current one stays (TASK-011 TAC-04).
                'revokeSessions' => ! $deactivated && ! $own && $sessions !== [],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function summary(User $user): array
    {
        return [
            'public_id' => $user->public_id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role_group' => $user->role_group,
            'status' => $user->status,
            'last_login_at' => $user->last_login_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function account(User $user): array
    {
        return [
            ...self::summary($user),
            'staff_identifier' => $user->staff_identifier,
            'locale' => $user->locale,
            'must_change_password' => $user->must_change_password,
            'lock_version' => $user->lock_version,
        ];
    }
}
