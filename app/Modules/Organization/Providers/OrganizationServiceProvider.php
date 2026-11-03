<?php

namespace App\Modules\Organization\Providers;

use App\Models\User;
use App\Modules\ModuleServiceProvider;
use App\Modules\Organization\Contracts\MaintenancePolicy;
use App\Modules\Organization\Support\UnresolvedMaintenancePolicy;
use Illuminate\Support\Facades\Gate;

class OrganizationServiceProvider extends ModuleServiceProvider
{
    /**
     * The capability of WF-029 F1/F2 (ACCESS_CONTROL section 4.1); its decision stays BLOCKED_BY_GAP-021.
     */
    public const MANAGE_ORGANIZATION = 'admin.org.manage';

    public function register(): void
    {
        $this->app->bind(MaintenancePolicy::class, UnresolvedMaintenancePolicy::class);
    }

    public function boot(): void
    {
        parent::boot();

        // Until the scoped grants of TASK-014 exist, only an active SUPER_ADMIN holds it (OD-10). An appointment never grants
        // it, nor any other permission (OD-28, TASK-013 TAC-03).
        Gate::define(self::MANAGE_ORGANIZATION, fn (User $user): bool => $user->mayHoldSession() && $user->role_group === User::SUPER_ADMIN);
    }
}
