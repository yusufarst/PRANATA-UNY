<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Events\AccountLocked;
use Illuminate\Support\Facades\RateLimiter;

/**
 * The temporary lock of an account after repeated failed sign-ins (SECURITY_MODEL section 3).
 *
 * After config('auth.lockout.attempts') failures within the window the account is locked for the lock minutes,
 * whatever address the attempts come from. The lock expires by itself, so it lives in the cache and not in
 * users.status, which holds only the LOCKED an administrator sets.
 */
class LoginLockout
{
    /**
     * Whether the account is locked now.
     */
    public function isLocked(User $user): bool
    {
        return RateLimiter::tooManyAttempts($this->lockKey($user), 1);
    }

    /**
     * Count one failed sign-in and lock the account when it reaches the limit.
     */
    public function recordFailure(User $user): void
    {
        $failures = RateLimiter::hit($this->failureKey($user), $this->minutes('window_minutes') * 60);

        if ($failures >= (int) config('auth.lockout.attempts')) {
            RateLimiter::hit($this->lockKey($user), $this->minutes('lock_minutes') * 60);
            RateLimiter::clear($this->failureKey($user));

            AccountLocked::dispatch($user, $this->minutes('lock_minutes'));
        }
    }

    /**
     * Forget the failures after a successful sign-in.
     */
    public function clear(User $user): void
    {
        RateLimiter::clear($this->failureKey($user));
    }

    private function failureKey(User $user): string
    {
        return 'identity-login-failures:'.$user->getKey();
    }

    private function lockKey(User $user): string
    {
        return 'identity-login-lock:'.$user->getKey();
    }

    private function minutes(string $key): int
    {
        return (int) config('auth.lockout.'.$key);
    }
}
