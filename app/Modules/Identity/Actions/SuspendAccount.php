<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\AccountSuspended;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Exceptions\StaleVersionException;
use App\Modules\Platform\Support\Action;
use Illuminate\Support\Str;

/**
 * Hold an account for a while, until an administrator reinstates it (TASK-011: buat, ubah, suspend, nonaktifkan;
 * SECURITY_MODEL section 2: SUSPENDED by an administrator).
 *
 * Every session of it ends and the remember token changes, so neither an open session nor an older remember cookie
 * comes back with the reinstatement. Its open one-time secrets stay: they still cannot sign in while it is suspended.
 */
final class SuspendAccount extends Action
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

            if (! in_array($user->status, [User::ACTIVE, User::LOCKED], true)) {
                throw DomainRuleViolation::because('identity.account_not_suspendable');
            }

            $user->forceFill([
                'status' => User::SUSPENDED,
                'remember_token' => Str::random(60),
            ])->saveAtVersion($seenVersion);

            $ended = $this->connection()
                ->table((string) config('session.table', 'sessions'))
                ->where('user_id', $user->getKey())
                ->delete();

            // Inside the transaction, so the suspension and its audit record commit or roll back together (fail closed).
            AccountSuspended::dispatch($user, $reason, $ended);
        });
    }
}
