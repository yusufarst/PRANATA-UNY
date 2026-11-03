<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Events\SignInFailed;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Timebox;

/**
 * Finds the account for a sign-in by username or email (SECURITY_MODEL section 3).
 *
 * Every refusal looks the same to the caller: an unknown identity, a wrong password, a locked account and an
 * account that is not ACTIVE all give the one generic message, and in about the same time.
 */
class CredentialAuthenticator
{
    /**
     * The least time one attempt takes, in microseconds, so its duration does not tell why it failed.
     */
    private const MINIMUM_MICROSECONDS = 200_000;

    public function __construct(private readonly LoginLockout $lockout) {}

    /**
     * Get the account that may sign in with the request's credentials, or null.
     */
    public function __invoke(Request $request): ?User
    {
        $identity = (string) $request->input('login');
        $password = (string) $request->input('password');

        return (new Timebox)->call(fn () => $this->authenticate($identity, $password), self::MINIMUM_MICROSECONDS);
    }

    private function authenticate(string $identity, string $password): ?User
    {
        $user = $this->find($identity);

        if ($user === null) {
            // Hash anyway, so an unknown identity costs as much as a known one.
            Hash::make($password);

            return $this->refuse(null, SignInFailed::UNKNOWN_IDENTITY);
        }

        if ($this->lockout->isLocked($user)) {
            return $this->refuse($user, SignInFailed::ACCOUNT_LOCKED);
        }

        if (! Hash::check($password, $user->password)) {
            $this->lockout->recordFailure($user);

            return $this->refuse($user, SignInFailed::WRONG_PASSWORD);
        }

        if ($user->status !== User::ACTIVE) {
            return $this->refuse($user, SignInFailed::NOT_ACTIVE);
        }

        $this->lockout->clear($user);

        $provider = Auth::guard()->getProvider();

        if ($provider instanceof EloquentUserProvider && config('hashing.rehash_on_login', true)) {
            $provider->rehashPasswordIfRequired($user, ['password' => $password]);
        }

        return $user;
    }

    /**
     * Report the refusal; the caller still gets only null.
     */
    private function refuse(?User $user, string $reason): null
    {
        SignInFailed::dispatch($user, $reason);

        return null;
    }

    /**
     * An address holds an @ and a username cannot, so the input names one column. Both match without regard to case.
     */
    private function find(string $identity): ?User
    {
        if ($identity === '') {
            return null;
        }

        $column = str_contains($identity, '@') ? 'email' : 'username';

        return User::query()->whereRaw("lower({$column}) = lower(?)", [$identity])->first();
    }
}
