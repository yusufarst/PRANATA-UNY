<?php

namespace App\Modules\Organization\Contracts;

use Carbon\CarbonImmutable;

/**
 * The gate of TR-285 (TASK-013 TAC-04): the policy family organization.maintenance (POLICY_REGISTRY section 1, GAP-021).
 */
interface MaintenancePolicy
{
    /**
     * Whether a version of organization.maintenance is officially in force on the reference date (POLICY_REGISTRY section 3.1).
     */
    public function isOfficialOn(CarbonImmutable $referenceDate): bool;

    /**
     * Whether, without such a version, the decision may still be recorded as a simulation. Never in production.
     */
    public function allowsSimulation(): bool;
}
