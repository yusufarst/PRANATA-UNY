<?php

namespace App\Modules\Organization\Providers;

use App\Modules\ModuleServiceProvider;
use App\Modules\Organization\Contracts\MaintenancePolicy;
use App\Modules\Organization\Support\UnresolvedMaintenancePolicy;

class OrganizationServiceProvider extends ModuleServiceProvider
{
    /**
     * The capability of WF-029 F1/F2 (ACCESS_CONTROL section 4.1); its decision stays BLOCKED_BY_GAP-021. The Access module
     * decides it from the role group and the grants; an appointment never grants it, nor any other permission (OD-28,
     * TASK-013 TAC-03).
     */
    public const MANAGE_ORGANIZATION = 'admin.org.manage';

    public function register(): void
    {
        $this->app->bind(MaintenancePolicy::class, UnresolvedMaintenancePolicy::class);
    }
}
