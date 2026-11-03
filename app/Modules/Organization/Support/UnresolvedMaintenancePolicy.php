<?php

namespace App\Modules\Organization\Support;

use App\Modules\Organization\Contracts\MaintenancePolicy;
use Carbon\CarbonImmutable;

/**
 * organization.maintenance before the Policy Registry exists: no version can be official yet, because none can be activated
 * while GAP-021 is open (POLICY_REGISTRY section 2.1). The Policy Registry Task binds its resolver in place of this class.
 */
final class UnresolvedMaintenancePolicy implements MaintenancePolicy
{
    public function isOfficialOn(CarbonImmutable $referenceDate): bool
    {
        return false;
    }

    public function allowsSimulation(): bool
    {
        return (bool) config('policy.allow_draft') && ! app()->isProduction();
    }
}
