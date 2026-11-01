<?php

namespace App\Modules\Platform\Providers;

use App\Modules\ModuleServiceProvider;
use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;

class PlatformServiceProvider extends ModuleServiceProvider
{
    /**
     * Bootstrap the module.
     */
    public function boot(): void
    {
        parent::boot();

        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if ($event->command === null || ! DatabaseResetGuard::isDestructive($event->command)) {
                return;
            }

            $connection = $event->input->getParameterOption('--database') ?: config('database.default');

            DatabaseResetGuard::assertAllowed(
                $this->app->environment(),
                config("database.connections.{$connection}.database"),
            );
        });
    }
}
