<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\SessionsRevoked;
use App\Modules\Identity\Support\ActiveSessions;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use App\Modules\Platform\Support\Action;
use Illuminate\Support\Str;

/**
 * End sessions of an account (TASK-011 TAC-04): one session or every other one, by the account itself, or all of them, by an
 * administrator. An ended session is refused on its next request.
 *
 * The remember token changes too, because one remember cookie cannot be told from another: a device that lost its session
 * must not sign in again with the cookie it kept. A session that stays keeps working; only its own remember cookie lapses.
 */
final class RevokeSessions extends Action
{
    /**
     * @param  string  $scope  a SessionsRevoked::ONE, ::OTHERS or ::ALL
     * @param  string|null  $key  the key of the one session, as ActiveSessions shows it
     * @param  string|null  $keepSessionId  the session that asked, which this command never ends
     * @return int the number of sessions ended
     */
    public function handle(User $user, string $scope, ?string $key = null, ?string $keepSessionId = null): int
    {
        return $this->transaction(function () use ($user, $scope, $key, $keepSessionId): int {
            $user = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();

            if ($user->status === User::DEACTIVATED) {
                throw DomainRuleViolation::because('identity.account_deactivated');
            }

            $sessions = ActiveSessions::query($this->connection(), $user);

            if ($scope === SessionsRevoked::ONE) {
                $id = $key === null ? null : ActiveSessions::idOf($this->connection(), $user, $key);

                if ($id === null || $id === $keepSessionId) {
                    throw DomainRuleViolation::because('identity.session_not_found');
                }

                $sessions->where('id', $id);
            } elseif ($keepSessionId !== null) {
                $sessions->where('id', '!=', $keepSessionId);
            }

            $ended = $sessions->delete();

            $user->forceFill(['remember_token' => Str::random(60)])->save();

            // Inside the transaction, so the change and its audit record commit or roll back together (fail closed).
            SessionsRevoked::dispatch($user, $scope, $ended);

            return $ended;
        });
    }
}
