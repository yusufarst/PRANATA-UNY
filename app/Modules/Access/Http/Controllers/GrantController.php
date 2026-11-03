<?php

namespace App\Modules\Access\Http\Controllers;

use App\Models\User;
use App\Modules\Access\Actions\ApproveGrant;
use App\Modules\Access\Actions\GrantAccess;
use App\Modules\Access\Actions\RevokeGrant;
use App\Modules\Access\Http\Requests\ApproveForm;
use App\Modules\Access\Http\Requests\GrantForm;
use App\Modules\Access\Http\Requests\RevokeForm;
use App\Modules\Access\Models\AccessGrant;
use App\Modules\Access\Models\AccessProfile;
use App\Modules\Access\Providers\AccessServiceProvider;
use App\Modules\Access\Support\CapabilityCatalog;
use App\Modules\Access\Support\ScopeType;
use App\Modules\Organization\Queries\UnitScope;
use App\Modules\Organization\Support\BusinessDate;
use App\Modules\Platform\Exceptions\StaleVersionException;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * PG-503 Akses (INFORMATION_ARCHITECTURE section 3.6, WF-002 TR-011 to TR-014): the list, detail and form of grants, their
 * approval and their revocation.
 *
 * Every route also carries can:access.administer; each method checks the capability of its own action again.
 */
class GrantController
{
    private const STATUSES = [AccessGrant::PENDING_APPROVAL, AccessGrant::ACTIVE, AccessGrant::REVOKED, AccessGrant::EXPIRED];

    public function index(Request $request): Response
    {
        Gate::authorize(AccessServiceProvider::ADMINISTER_ACCESS);

        $filters = [
            'q' => trim($request->string('q')->value()),
            'status' => in_array($request->query('status'), self::STATUSES, true) ? $request->query('status') : '',
        ];
        $today = BusinessDate::today()->toDateString();

        $grants = AccessGrant::query()
            ->with(['user:id,public_id,username,name', 'profile:id,code,name'])
            ->when($filters['q'] !== '', function ($query) use ($filters): void {
                $term = '%'.addcslashes(mb_strtolower($filters['q']), '\\%_').'%';

                $query->whereHas('user', fn ($query) => $query->where(fn ($query) => $query
                    ->whereRaw('lower(username) like ?', [$term])
                    ->orWhereRaw('lower(name) like ?', [$term])));
            })
            ->when($filters['status'] !== '', fn ($query) => $query->withStatusOn($filters['status'], $today))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $units = UnitScope::labels($this->unitIds(array_values($grants->getCollection()->all())));

        return Inertia::render('admin/access/index', [
            'grants' => $grants->through(fn (AccessGrant $grant): array => self::summary($grant, $units)),
            'filters' => $filters,
            'pendingCount' => AccessGrant::query()->withStatusOn(AccessGrant::PENDING_APPROVAL, $today)->count(),
            'can' => ['grant' => $request->user()?->can('admin.access.grant') ?? false],
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('admin.access.grant');

        $publicId = $request->query('user');
        $user = is_string($publicId)
            ? User::query()->where('public_id', $publicId)->first(['id', 'public_id', 'username', 'name'])
            : null;

        return Inertia::render('admin/access/form', [
            // Opened from the detail of a user, the form names that user and its cancel leads back there.
            'user' => $user === null ? null : ['public_id' => $user->public_id, 'username' => $user->username, 'name' => $user->name],
            'canOpenUser' => $request->user()?->can('admin.users.manage') ?? false,
            'today' => BusinessDate::today()->toDateString(),
            'options' => $this->options(),
        ]);
    }

    public function store(GrantForm $request, GrantAccess $grantAccess): RedirectResponse
    {
        Gate::authorize('admin.access.grant');

        $user = User::query()->where('username', $request->string('username')->trim()->value())->first();

        if ($user === null) {
            throw ValidationException::withMessages(['username' => __('access.user_unknown')]);
        }

        $profile = null;

        if ($request->input('target') === GrantForm::PROFILE) {
            $profile = AccessProfile::query()->where('code', $request->string('profile')->value())->first();

            if ($profile === null) {
                throw ValidationException::withMessages(['profile' => __('access.profile_unknown')]);
            }
        }

        $scope = ScopeType::from($request->string('scope_type')->value());
        $unit = $request->string('unit')->value();

        $grant = $grantAccess->handle(
            $request->user(),
            $user,
            $profile,
            $profile === null ? $request->string('capability')->value() : null,
            $scope,
            $unit === '' ? null : UnitScope::idOf($unit),
            $request->filled('scope_key') ? $request->string('scope_key')->value() : null,
            CarbonImmutable::createFromFormat('!Y-m-d', $request->string('effective_from')->value()) ?: BusinessDate::today(),
            $request->filled('effective_to') ? (CarbonImmutable::createFromFormat('!Y-m-d', $request->string('effective_to')->value()) ?: null) : null,
            $request->string('reason')->trim()->value(),
        );

        // TAC-03: a privileged grant waits for a second person; the toast says so.
        Inertia::flash('toast', ['type' => 'success', 'message' => __($grant->status === AccessGrant::PENDING_APPROVAL
            ? 'access.grant_awaits_approval'
            : 'access.grant_saved')]);

        return to_route('access.admin.grants.index');
    }

    public function show(Request $request, AccessGrant $grant): Response
    {
        Gate::authorize(AccessServiceProvider::ADMINISTER_ACCESS);

        return Inertia::render('admin/access/show', $this->detail($request, $grant));
    }

    public function approve(ApproveForm $request, AccessGrant $grant, ApproveGrant $approveGrant): RedirectResponse
    {
        Gate::authorize('admin.access.approve');

        try {
            $approveGrant->handle($request->user(), $grant, $request->integer('lock_version'));
        } catch (StaleVersionException $exception) {
            throw $exception->showing('admin/access/show', $this->detail($request, $grant->fresh() ?? $grant));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('access.grant_approved')]);

        return to_route('access.admin.grants.show', $grant->public_id);
    }

    public function revoke(RevokeForm $request, AccessGrant $grant, RevokeGrant $revokeGrant): RedirectResponse
    {
        Gate::authorize('admin.access.grant');

        try {
            $revokeGrant->handle($request->user(), $grant, $request->string('reason')->trim()->value(), $request->integer('lock_version'));
        } catch (StaleVersionException $exception) {
            throw $exception->showing('admin/access/show', $this->detail($request, $grant->fresh() ?? $grant));
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('access.grant_revoked')]);

        return to_route('access.admin.grants.show', $grant->public_id);
    }

    /**
     * @param  array<int, array{public_id: string, code: string, name: string}>  $units
     * @return array<string, mixed>
     */
    private static function summary(AccessGrant $grant, array $units): array
    {
        $unit = self::namesUnit($grant) ? ($units[(int) $grant->scope_id] ?? null) : null;

        return [
            'public_id' => $grant->public_id,
            'user' => [
                'public_id' => $grant->user->public_id,
                'username' => $grant->user->username,
                'name' => $grant->user->name,
            ],
            'profile' => $grant->profile === null ? null : ['code' => $grant->profile->code, 'name' => $grant->profile->name],
            'capability' => $grant->capability,
            'scope_type' => $grant->scope_type->value,
            'scope_unit' => $unit,
            'scope_id' => $grant->scope_type === ScopeType::Warehouse ? $grant->scope_id : null,
            'scope_key' => $grant->scope_key,
            'requires_approval' => $grant->requires_approval,
            // A grant past its end shows EXPIRED, as the evaluator already treats it.
            'status' => $grant->statusOn(BusinessDate::today()->toDateString()),
            'effective_from' => $grant->effective_from->toDateString(),
            'effective_to' => $grant->effective_to?->toDateString(),
            'created_at' => $grant->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function detail(Request $request, AccessGrant $grant): array
    {
        $grant->loadMissing(['user:id,public_id,username,name,role_group,status', 'profile']);
        $actor = $request->user();
        $people = User::query()
            ->whereIn('id', array_filter([$grant->granted_by, $grant->approved_by, $grant->revoked_by]))
            ->pluck('name', 'id');
        $own = $actor !== null && $grant->user_id === $actor->id;
        $status = $grant->statusOn(BusinessDate::today()->toDateString());

        return [
            'grant' => [
                ...self::summary($grant, UnitScope::labels($this->unitIds([$grant]))),
                'capabilities' => $grant->profile === null ? [(string) $grant->capability] : $grant->profile->capabilities(),
                'reason' => $grant->reason,
                'granted_by' => $people[$grant->granted_by] ?? null,
                'approved_by' => $grant->approved_by === null ? null : ($people[$grant->approved_by] ?? null),
                'approved_at' => $grant->approved_at?->toIso8601String(),
                'revoked_by' => $grant->revoked_by === null ? null : ($people[$grant->revoked_by] ?? null),
                'revoked_at' => $grant->revoked_at?->toIso8601String(),
                'revoke_reason' => $grant->revoke_reason,
                'lock_version' => $grant->lock_version,
            ],
            'canOpenUser' => $actor?->can('admin.users.manage') ?? false,
            // SOD-05, TAC-03: the second person is neither the granter nor the receiver; the server checks this again.
            'can' => [
                'approve' => $status === AccessGrant::PENDING_APPROVAL
                    && ($actor?->can('admin.access.approve') ?? false)
                    && $grant->granted_by !== $actor->id
                    && ! $own,
                'revoke' => in_array($status, [AccessGrant::PENDING_APPROVAL, AccessGrant::ACTIVE], true)
                    && ($actor?->can('admin.access.grant') ?? false),
            ],
            'approvalBlocked' => $status === AccessGrant::PENDING_APPROVAL && ($grant->granted_by === $actor?->id || $own),
        ];
    }

    /**
     * The choices of the form: the active profiles with their capabilities, each capability a grant may give with its scopes,
     * and the units of a unit scope. Warehouses join with the Inventory module.
     *
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'profiles' => AccessProfile::query()
                ->where('status', AccessProfile::ACTIVE)
                ->orderBy('name')
                ->get()
                ->map(function (AccessProfile $profile): array {
                    $capabilities = $profile->capabilities();
                    $scopes = [];

                    foreach ($capabilities as $capability) {
                        foreach (CapabilityCatalog::scopes($capability) as $scope) {
                            $scopes[$scope->value] = true;
                        }
                    }

                    return [
                        'code' => $profile->code,
                        'name' => $profile->name,
                        'description' => $profile->description,
                        'is_privileged' => $profile->is_privileged,
                        'capabilities' => $capabilities,
                        'scopes' => array_values(array_filter(array_keys($scopes), fn (string $scope): bool => $scope !== ScopeType::Warehouse->value)),
                    ];
                })
                ->all(),
            'capabilities' => array_values(array_map(
                fn (string $capability): array => [
                    'value' => $capability,
                    'scopes' => array_values(array_map(
                        fn (ScopeType $scope): string => $scope->value,
                        array_filter(CapabilityCatalog::scopes($capability), fn (ScopeType $scope): bool => $scope !== ScopeType::Warehouse),
                    )),
                    'privileged' => array_values(array_map(
                        fn (ScopeType $scope): string => $scope->value,
                        array_filter(CapabilityCatalog::scopes($capability), fn (ScopeType $scope): bool => CapabilityCatalog::isPrivileged($capability, $scope)),
                    )),
                ],
                array_filter(CapabilityCatalog::all(), fn (string $capability): bool => array_filter(
                    CapabilityCatalog::scopes($capability),
                    fn (ScopeType $scope): bool => $scope !== ScopeType::Warehouse,
                ) !== []),
            )),
            'units' => UnitScope::choices(),
        ];
    }

    /**
     * @param  list<AccessGrant>  $grants
     * @return list<int>
     */
    private function unitIds(array $grants): array
    {
        $ids = [];

        foreach ($grants as $grant) {
            if (self::namesUnit($grant)) {
                $ids[] = (int) $grant->scope_id;
            }
        }

        return array_values(array_unique($ids));
    }

    private static function namesUnit(AccessGrant $grant): bool
    {
        return in_array($grant->scope_type, [ScopeType::OrgUnit, ScopeType::OrgUnitSubtree], true) && $grant->scope_id !== null;
    }
}
