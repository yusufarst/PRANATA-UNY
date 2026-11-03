<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\TwoFactorResetRequested;
use App\Modules\Identity\Models\TwoFactorReset;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;

/**
 * Ask to remove the TOTP of an account whose owner lost their device, after the administrator checked who asked (SECURITY_MODEL
 * section 3.1, TASK-012 TAC-03).
 *
 * Every request waits for a second administrator ({@see ApproveTwoFactorReset}), whatever the account. No administrator asks
 * for their own account.
 */
final class RequestTwoFactorReset extends Action
{
    public function handle(User $actor, User $user, string $identityCheckMethod, ?string $identityCheckNote): TwoFactorReset
    {
        return $this->transaction(function () use ($actor, $user, $identityCheckMethod, $identityCheckNote): TwoFactorReset {
            $user = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($user->is($actor)) {
                throw DomainRuleViolation::because('identity.cannot_reset_own_two_factor');
            }

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            if ($user->two_factor_secret === null) {
                throw DomainRuleViolation::because('identity.two_factor_not_set_up');
            }

            if (TwoFactorReset::pendingFor($user) !== null) {
                throw DomainRuleViolation::because('identity.two_factor_reset_already_pending');
            }

            // The request waits as long as a one-time secret would live; a second administrator approves it before then.
            $reset = TwoFactorReset::query()->create([
                'user_id' => $user->getKey(),
                'requested_by' => $actor->getKey(),
                'identity_check_method' => $identityCheckMethod,
                'identity_check_note' => $identityCheckNote,
                'status' => TwoFactorReset::PENDING_APPROVAL,
                'expires_at' => now()->addHours((int) config('auth.one_time_secret_hours')),
            ]);

            // Inside the transaction, so the request and its audit record commit or roll back together (fail closed).
            TwoFactorResetRequested::dispatch($reset);

            return $reset;
        });
    }
}
