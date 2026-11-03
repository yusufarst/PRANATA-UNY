<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Events\PasswordResetRevoked;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;

/**
 * Withdraw, or turn down, an assisted reset that waits for a second administrator (TASK-011 TAC-03). The password of the
 * account does not change.
 */
final class RevokeAssistedReset extends Action
{
    public function handle(PasswordReset $reset): void
    {
        $this->transaction(function () use ($reset): void {
            $reset = PasswordReset::query()->whereKey($reset->getKey())->lockForUpdate()->firstOrFail();

            if ($reset->status !== PasswordReset::PENDING_APPROVAL || ! $reset->isOpen()) {
                throw DomainRuleViolation::because('identity.reset_not_pending');
            }

            $reset->forceFill(['status' => PasswordReset::REVOKED])->save();

            // Inside the transaction, so the change and its audit record commit or roll back together (fail closed).
            PasswordResetRevoked::dispatch($reset);
        });
    }
}
