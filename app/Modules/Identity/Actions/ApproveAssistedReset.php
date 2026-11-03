<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\PasswordResetIssued;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Identity\Support\TemporaryPassword;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Carbon\CarbonImmutable;

/**
 * The second administrator approves the assisted reset of a privileged account and gets its temporary password, shown once,
 * to hand over (SECURITY_MODEL section 3.1, TASK-011 TAC-03). The approver is neither the requester nor the account itself;
 * the database refuses that too.
 */
final class ApproveAssistedReset extends Action
{
    /**
     * @return array{password: string, expires_at: CarbonImmutable}
     */
    public function handle(User $approver, PasswordReset $reset): array
    {
        return $this->transaction(function () use ($approver, $reset): array {
            $reset = PasswordReset::query()->whereKey($reset->getKey())->lockForUpdate()->firstOrFail();

            if ($reset->status !== PasswordReset::PENDING_APPROVAL || ! $reset->isOpen()) {
                throw DomainRuleViolation::because('identity.reset_not_pending');
            }

            if ($approver->getKey() === $reset->requested_by || $approver->getKey() === $reset->user_id) {
                throw DomainRuleViolation::because('identity.reset_needs_second_admin');
            }

            $user = User::query()->whereKey($reset->user_id)->lockForUpdate()->firstOrFail();

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            $reset->forceFill(['approved_by' => $approver->getKey()]);
            $issued = TemporaryPassword::issue($this->connection(), $user, $reset);

            // Inside the transaction, so the reset and its audit record commit or roll back together (fail closed).
            PasswordResetIssued::dispatch($reset, $issued['ended_sessions']);

            return ['password' => $issued['password'], 'expires_at' => $issued['expires_at']];
        });
    }
}
