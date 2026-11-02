<?php

namespace App\Modules\Audit\Providers;

use App\Modules\Audit\Console\EnsureAuditPartitionsCommand;
use App\Modules\Audit\Support\AuditLogger;
use App\Modules\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class AuditServiceProvider extends ModuleServiceProvider
{
    /**
     * Register the module services.
     */
    public function register(): void
    {
        $this->app->scoped(AuditLogger::class);
    }

    /**
     * Bootstrap the module.
     */
    public function boot(): void
    {
        parent::boot();

        if ($this->app->runningInConsole()) {
            $this->commands([EnsureAuditPartitionsCommand::class]);
        }

        // Daily, so a missed run or a new server catches up long before a month starts (OPERATIONS_RUNBOOK RB-05).
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command(EnsureAuditPartitionsCommand::class)->dailyAt('01:15');
        });
    }
}
