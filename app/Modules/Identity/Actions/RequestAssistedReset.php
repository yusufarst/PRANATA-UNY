<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\PasswordResetIssued;
use App\Modules\Identity\Events\PasswordResetRequested;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Identity\Support\PrivilegedAccounts;
use App\Modules\Identity\Support\TemporaryPassword;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Carbon\CarbonImmutable;

/**
 * Reset the password of an account for its owner, after the administrator checked who asked (SECURITY_MODEL section 3.1,
 * OPERATIONS_RUNBOOK RB-08, TASK-011 TAC-02/TAC-03).
 *
 * The check method is recorded. An ordinary account gets its temporary password at once. A privileged account only gets a
 * request that waits for a second administrator ({@see ApproveAssistedReset}). No administrator resets their own account.
 */
final class RequestAssistedReset extends Action
{
    public function __construct(private readonly PrivilegedAccounts $privileged) {}

    /**
     * @return array{reset: PasswordReset, password: ?string, expires_at: CarbonImmutable}
     */
    public function handle(User $actor, User $user, string $identityCheckMethod, ?string $identityCheckNote): array
    {
        return $this->transaction(function () use ($actor, $user, $identityCheckMethod, $identityCheckNote): array {
            $user = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($user->is($actor)) {
                throw DomainRuleViolation::because('identity.cannot_reset_self');
            }

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            $reset = new PasswordReset([
                'user_id' => $user->getKey(),
                'channel' => PasswordReset::ADMIN_ASSISTED,
                'requested_by' => $actor->getKey(),
                'identity_check_method' => $identityCheckMethod,
                'identity_check_note' => $identityCheckNote,
            ]);

            if ($this->privileged->isPrivileged($user)) {
                $pending = PasswordReset::query()
                    ->where('user_id', $user->getKey())
                    ->where('status', PasswordReset::PENDING_APPROVAL)
                    ->where('expires_at', '>', now())
                    ->exists();

                if ($pending) {
                    throw DomainRuleViolation::because('identity.reset_already_pending');
                }

                // The request waits as long as a secret would live; a second administrator approves it before then.
                $expiresAt = CarbonImmutable::now()->addHours((int) config('auth.one_time_secret_hours'));
                $reset->forceFill(['status' => PasswordReset::PENDING_APPROVAL, 'expires_at' => $expiresAt])->save();

                PasswordResetRequested::dispatch($reset);

                return ['reset' => $reset, 'password' => null, 'expires_at' => $expiresAt];
            }

            $issued = TemporaryPassword::issue($this->connection(), $user, $reset);

            // Inside the transaction, so the reset and its audit record commit or roll back together (fail closed).
            PasswordResetIssued::dispatch($reset, $issued['ended_sessions']);

            return ['reset' => $reset, 'password' => $issued['password'], 'expires_at' => $issued['expires_at']];
        });
    }
}
