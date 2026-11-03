<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Events\SignInFailed;
use Illuminate\Http\Exceptions\HttpResponseException;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;
use Laravel\Fortify\Events\ValidTwoFactorAuthenticationCodeProvided;
use Laravel\Fortify\Fortify;

/**
 * The TOTP challenge is the second half of a sign-in, so the account lock and the status gate hold there too (SECURITY_MODEL
 * section 3, SEC-02, TASK-012 TAC-01). A wrong code or recovery code counts as a failed sign-in; the right password alone
 * no longer clears the failures, only a finished sign-in does ({@see LoginLockout::clear()} on Login).
 */
final class TwoFactorChallengeGuard
{
    public function __construct(private readonly LoginLockout $lockout) {}

    /**
     * Counts the wrong code; once the account locks, the challenge ends and the password is asked again, which the lock refuses.
     */
    public function failed(TwoFactorAuthenticationFailed $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->lockout->recordFailure($user);

        if ($this->lockout->isLocked($user)) {
            $this->endChallenge();
        }
    }

    /**
     * Refuses a right code for an account that locked or stopped being ACTIVE since its password was checked.
     */
    public function passed(ValidTwoFactorAuthenticationCodeProvided $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $reason = match (true) {
            $this->lockout->isLocked($user) => SignInFailed::ACCOUNT_LOCKED,
            $user->status !== User::ACTIVE => SignInFailed::NOT_ACTIVE,
            default => null,
        };

        if ($reason === null) {
            return;
        }

        SignInFailed::dispatch($user, $reason);
        $this->endChallenge();

        // The same generic answer as a refused password (SECURITY_MODEL section 3).
        throw new HttpResponseException(
            redirect()->route('login')->withErrors([Fortify::username() => __('auth.failed')]),
        );
    }

    private function endChallenge(): void
    {
        request()->session()->forget(['login.id', 'login.remember']);
    }
}
