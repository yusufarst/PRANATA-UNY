<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\TwoFactorResetRevoked;
use App\Modules\Identity\Models\TwoFactorReset;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;

/**
 * Withdraw, or turn down, a request to remove the TOTP of an account (TASK-012 TAC-03). The TOTP of the account stays. The
 * account itself cannot turn down a request meant to recover it.
 */
final class RevokeTwoFactorReset extends Action
{
    public function handle(User $actor, TwoFactorReset $reset): void
    {
        $this->transaction(function () use ($actor, $reset): void {
            $reset = TwoFactorReset::query()->whereKey($reset->getKey())->lockForUpdate()->firstOrFail();

            if ($reset->user_id === $actor->getKey()) {
                throw DomainRuleViolation::because('identity.cannot_revoke_own_reset');
            }

            if (! $reset->isOpen()) {
                throw DomainRuleViolation::because('identity.two_factor_reset_not_pending');
            }

            $reset->forceFill(['status' => TwoFactorReset::REVOKED, 'decided_at' => now()])->save();

            // Inside the transaction, so the change and its audit record commit or roll back together (fail closed).
            TwoFactorResetRevoked::dispatch($reset);
        });
    }
}
