<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\AccountReinstated;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;

/**
 * Lift the suspension of an account, so it signs in again with its own password (TASK-011). A deactivated account never
 * comes back (OD-38).
 */
final class ReinstateAccount extends Action
{
    /**
     * A {@see StaleVersionException} from inside the transaction reaches the caller when someone changed the account first.
     */
    public function handle(User $actor, User $user, string $reason, int $seenVersion): void
    {
        $this->transaction(function () use ($actor, $user, $reason, $seenVersion): void {
            $user->refresh();

            if ($user->is($actor)) {
                throw DomainRuleViolation::because('identity.cannot_suspend_self');
            }

            if ($user->status !== User::SUSPENDED) {
                throw DomainRuleViolation::because('identity.account_not_suspended');
            }

            $user->forceFill(['status' => User::ACTIVE])->saveAtVersion($seenVersion);

            // Inside the transaction, so the change and its audit record commit or roll back together (fail closed).
            AccountReinstated::dispatch($user, $reason);
        });
    }
}
