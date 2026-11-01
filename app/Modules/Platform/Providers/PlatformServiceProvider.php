<?php

namespace App\Modules\Platform\Providers;

use App\Modules\ModuleServiceProvider;
use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\DB;
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
     * Guard destructive database commands in two layers.
     *
     * The prohibition covers the default connection in every environment. The listener
     * also covers --database, but Laravel does not fire this event when APP_ENV=testing.
     */
    private function guardDestructiveCommands(): void
    {
        $default = config('database.default');

        DB::prohibitDestructiveCommands(! DatabaseResetGuard::allows(
            $this->app->environment(),
            config("database.connections.{$default}.database"),
        ));

        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if (! in_array($event->command, DatabaseResetGuard::COMMANDS, true)) {
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
