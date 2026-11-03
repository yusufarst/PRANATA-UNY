<?php

namespace App\Modules\Identity\Support;

use App\Models\User;
use App\Modules\Identity\Events\SignInFailed;
use App\Modules\Identity\Models\PasswordReset;
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

        /** @var User|null $user */
        $user = (new Timebox)->call(fn () => $this->authenticate($identity, $password), self::MINIMUM_MICROSECONDS);

        // A SUPER_ADMIN never gets the remember cookie (SECURITY_MODEL section 4). Fortify reads "remember" from the
        // request after this callback, both for the sign-in and for the two-factor challenge, so it is turned off here.
        if ($user?->role_group === User::SUPER_ADMIN) {
            $request->merge(['remember' => false]);
        }

        return $user;
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
            // Hash anyway, so a locked account answers as slowly as an unknown identity.
            Hash::make($password);

            return $this->refuse($user, SignInFailed::ACCOUNT_LOCKED);
        }

        if (! Hash::check($password, $user->password)) {
            $this->lockout->recordFailure($user);

            return $this->refuse($user, SignInFailed::WRONG_PASSWORD);
        }

        if ($user->status !== User::ACTIVE) {
            return $this->refuse($user, SignInFailed::NOT_ACTIVE);
        }

        if ($this->usesExpiredSecret($user)) {
            return $this->refuse($user, SignInFailed::SECRET_EXPIRED);
        }

        $this->lockout->clear($user);

        $provider = Auth::guard()->getProvider();

        // A one-time secret keeps its hash until the forced change: its record is found by that hash.
        if ($provider instanceof EloquentUserProvider && ! $user->must_change_password && config('hashing.rehash_on_login', true)) {
            $provider->rehashPasswordIfRequired($user, ['password' => $password]);
        }

        return $user;
    }

    /**
     * Whether the password is a one-time secret from an administrator that is past its life (SECURITY_MODEL section 3.1).
     * Such a secret is the password only until the forced change, and its record holds the same hash.
     */
    private function usesExpiredSecret(User $user): bool
    {
        if (! $user->must_change_password) {
            return false;
        }

        $secret = PasswordReset::query()
            ->where('user_id', $user->getKey())
            ->where('status', PasswordReset::ISSUED)
            ->where('temp_secret_hash', $user->password)
            ->latest('id')
            ->first();

        if ($secret === null || $secret->expires_at->isFuture()) {
            return false;
        }

        $secret->forceFill(['status' => PasswordReset::EXPIRED])->save();

        return true;
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
