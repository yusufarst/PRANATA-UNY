<?php

namespace App\Modules\Identity\Actions;

use App\Models\User;
use App\Modules\Identity\Events\PasswordChanged;
use App\Modules\Platform\Support\Action;
use Illuminate\Support\Str;

/**
 * Give an account a new password and end every other session of it (SECURITY_MODEL section 3, TASK-010 TAC-04/TAC-05).
 *
 * The forced change is then done. Other devices lose their database session and their remember cookie, because the
 * remember token changes; the session that made the change, if any, stays.
 */
final class ChangePassword extends Action
{
    /**
     * @param  string  $via  a PasswordChanged::FORCED, ::SETTINGS or ::RESET
     * @param  string|null  $keepSessionId  the session that made the change, or null to end them all
     */
    public function handle(User $user, string $password, string $via, ?string $keepSessionId = null): void
    {
        $this->transaction(function () use ($user, $password, $via, $keepSessionId): void {
            // A retry starts from the stored row: the save of a rolled-back attempt left nothing dirty.
            $user->refresh();

            $user->forceFill([
                'password' => $password,
                'must_change_password' => false,
                'password_changed_at' => now(),
                'remember_token' => Str::random(60),
            ])->save();

            $sessions = $this->connection()
                ->table((string) config('session.table', 'sessions'))
                ->where('user_id', $user->getKey());

            if ($keepSessionId !== null) {
                $sessions->where('id', '!=', $keepSessionId);
            }

            $ended = $sessions->delete();

            // Inside the transaction: the audit listener writes on the same connection, so the change and its audit
            // record commit or roll back together (fail closed).
            PasswordChanged::dispatch($user, $via, $ended);
        });
    }
}
