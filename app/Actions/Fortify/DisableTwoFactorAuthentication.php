<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Modules\Identity\Support\TwoFactorPolicy;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication as FortifyDisableTwoFactorAuthentication;

/**
 * An account that must have TOTP cannot turn it off itself (SEC-03, TASK-012 TAC-02); a lost device goes through a recovery
 * code or the reset by two administrators (TAC-03). The security page hides the button; this refuses the request.
 */
class DisableTwoFactorAuthentication extends FortifyDisableTwoFactorAuthentication
{
    public function __construct(private readonly TwoFactorPolicy $policy) {}

    /**
     * @param  mixed  $user
     */
    public function __invoke($user): void
    {
        // A setup that was never confirmed may still be dropped and started over.
        if ($user instanceof User && $this->policy->isRequiredFor($user) && $user->hasEnabledTwoFactorAuthentication()) {
            throw DomainRuleViolation::because('identity.two_factor_cannot_disable');
        }

        parent::__invoke($user);
    }
}
