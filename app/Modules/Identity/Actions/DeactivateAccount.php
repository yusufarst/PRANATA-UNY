<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\AccountDeactivated;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;
use Illuminate\Support\Str;

/**
 * End an account for good without deleting it (OD-38, OPERATIONS_RUNBOOK RB-13, TASK-011 TAC-06).
 *
 * The account becomes DEACTIVATED with the moment noted, every session of it ends, the remember token changes so an older
 * remember cookie no longer signs in, and its open one-time secrets are revoked. The row and its audit history stay.
 */
final class DeactivateAccount extends Action
{
    /**
     * @throws StaleVersionException when someone changed the account first
     */
    public function handle(User $actor, User $user, string $reason, int $seenVersion): void
    {
        $this->transaction(function () use ($actor, $user, $reason, $seenVersion): void {
            $user->refresh();

            if ($user->is($actor)) {
                throw DomainRuleViolation::because('identity.cannot_deactivate_self');
            }

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            $user->forceFill([
                'status' => User::DEACTIVATED,
                'deactivated_at' => now(),
                'remember_token' => Str::random(60),
            ])->saveAtVersion($seenVersion);

            $ended = $this->connection()
                ->table((string) config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();

            $revoked = PasswordReset::query()
                ->where('user_id', $user->getKey())
                ->whereIn('status', [PasswordReset::PENDING_APPROVAL, PasswordReset::ISSUED])
                ->update(['status' => PasswordReset::REVOKED]);

            // Inside the transaction, so the deactivation and its audit record commit or roll back together (fail closed).
            AccountDeactivated::dispatch($user, $reason, $ended, $revoked);
        });
    }
}
