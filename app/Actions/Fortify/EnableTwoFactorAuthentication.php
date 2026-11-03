<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Modules\Identity\Support\TwoFactorPolicy;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication as FortifyEnableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;

/**
 * Fortify's "force" replaces the secret of a confirmed TOTP and keeps it confirmed, so an account that must have TOTP would
 * swap in a secret no code ever proved (SEC-03, TASK-012 TAC-02). Such an account cannot; a lost device goes through a
 * recovery code or the reset by two administrators (TAC-03). The security page never sends "force".
 */
class EnableTwoFactorAuthentication extends FortifyEnableTwoFactorAuthentication
{
    public function __construct(TwoFactorAuthenticationProvider $provider, private readonly TwoFactorPolicy $policy)
    {
        parent::__construct($provider);
    }

    /**
     * @param  mixed  $user
     * @param  bool  $force
     */
    public function __invoke($user, $force = false): void
    {
        if ($force === true && $user instanceof User && $this->policy->isRequiredFor($user) && $user->hasEnabledTwoFactorAuthentication()) {
            throw DomainRuleViolation::because('identity.two_factor_cannot_disable');
        }

        parent::__invoke($user, $force);
    }
}
