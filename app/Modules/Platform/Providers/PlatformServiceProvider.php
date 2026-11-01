<?php

namespace App\Modules\Platform\Providers;

use App\Modules\ModuleServiceProvider;
use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Facades\Event;

class PlatformServiceProvider extends ModuleServiceProvider
{
    /**
     * Bootstrap the module.
     */
    public function boot(): void
    {
        parent::boot();

        $this->guardDestructiveCommands();
    }

    /**
     * Run the reset guard before every destructive command, in every environment.
     */
    private function guardDestructiveCommands(): void
    {
        // Laravel forwards console events only outside unit tests; APP_ENV=testing needs the guard as well.
        $kernel = $this->app->make(KernelContract::class);

        if ($this->app->runningUnitTests() && $kernel instanceof Kernel) {
            $kernel->rerouteSymfonyCommandEvents();
        }

        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if (! in_array($event->command, DatabaseResetGuard::COMMANDS, true)) {
                return;
            }

            $connection = $event->input->getParameterOption('--database') ?: config('database.default');

            DatabaseResetGuard::assertAllowed(
                $this->app->environment(),
                DatabaseResetGuard::databaseName(config("database.connections.{$connection}")),
            );
        });
    }
}
