<?php

namespace App\Modules\Identity\Support;

use App\Models\User;

/**
 * Whether an account must have confirmed TOTP (SECURITY_MODEL section 3, SEC-D1 as decided in D-12, SEC-03).
 *
 * While config('auth.two_factor_required') holds, every privileged account must; any other account may.
 */
final class TwoFactorPolicy
{
    public function __construct(private readonly PrivilegedAccounts $privileged) {}

    public function isRequiredFor(User $user): bool
    {
        return (bool) config('auth.two_factor_required') && $this->privileged->isPrivileged($user);
    }

    /**
     * Whether the account still has to set up TOTP before it may go on.
     */
    public function isMissingFor(User $user): bool
    {
        return $this->isRequiredFor($user) && ! $user->hasEnabledTwoFactorAuthentication();
    }
}
