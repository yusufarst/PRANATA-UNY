<?php

namespace App\Modules\Platform\Providers;

use App\Modules\ModuleServiceProvider;
use App\Modules\Platform\Console\PruneIdempotencyKeys;
use App\Modules\Platform\Support\DatabaseResetGuard;
use App\Modules\Platform\Support\DatabaseRoles;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Console\Input\InputInterface;

class PlatformServiceProvider extends ModuleServiceProvider
{
    private const MIGRATOR = 'migrator';

    /**
     * The application connection a running migration command replaced as the default, if any.
     */
    private ?string $replacedDefault = null;

    /**
     * The input of the command that replaced the default; a command it calls, such as db:wipe, must not restore it.
     */
    private ?InputInterface $replacingInput = null;

    /**
     * Bootstrap the module.
     */
    public function boot(): void
    {
        parent::boot();

        $this->guardDestructiveCommands();
        $this->runMigrationsAsOwner();
        $this->checkApplicationRole();
        $this->pruneIdempotencyKeys();
    }

    /**
     * Delete the expired idempotency keys every hour.
     */
    private function pruneIdempotencyKeys(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([PruneIdempotencyKeys::class]);

        $this->callAfterResolving(Schedule::class, function (Schedule $schedule): void {
            $schedule->command(PruneIdempotencyKeys::class)->hourly()->withoutOverlapping();
        });
    }

    /**
     * Fail the health check while the application connection holds more than the privileges of pranata_app (RB-16).
     */
    private function checkApplicationRole(): void
    {
        Event::listen(DiagnosingHealth::class, function (): void {
            if ((bool) config('database.application_role_required')) {
                DatabaseRoles::assertApplicationSession(DB::connection());
            }
        });
    }

    /**
     * Run the reset guard before every destructive command, in every environment.
     */
    private function guardDestructiveCommands(): void
    {
        // Laravel forwards console events only outside unit tests; APP_ENV=testing needs the guard as well.
        if ($this->app->runningUnitTests()) {
            $this->app->make(Kernel::class)->rerouteSymfonyCommandEvents();
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

    /**
     * Send every migration command through the migrator connection, unless it names another one (DATA_MODEL section 19).
     *
     * Registered after the reset guard, so a refused command never opens a migrator session.
     */
    private function runMigrationsAsOwner(): void
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event): void {
            if (! self::isMigrationCommand((string) $event->command) || $this->replacedDefault !== null) {
                return;
            }

            $requested = $event->input->getParameterOption('--database') ?: null;
            $application = (string) config('database.default');

            if (($requested !== null && $requested !== self::MIGRATOR) || ! $this->followApplicationDatabase($application)) {
                return;
            }

            // The reset guard checked the connection the command names; with DB_MIGRATOR_URL the migrator reaches another database.
            if (in_array($event->command, DatabaseResetGuard::COMMANDS, true)) {
                DatabaseResetGuard::assertAllowed(
                    $this->app->environment(),
                    DatabaseResetGuard::databaseName(config('database.connections.'.self::MIGRATOR)),
                );
            }

            if ($requested === null) {
                config(['database.default' => self::MIGRATOR]);
                $this->replacedDefault = $application;
                $this->replacingInput = $event->input;
            }

            $role = config('database.connections.'.self::MIGRATOR.'.role');

            DatabaseRoles::prepareMigrator(
                DB::connection(self::MIGRATOR),
                is_string($role) ? $role : null,
                ! $this->app->environment('production'),
            );
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event): void {
            if ($this->replacedDefault === null || $event->input !== $this->replacingInput) {
                return;
            }

            config(['database.default' => $this->replacedDefault]);
            $this->replacedDefault = null;
            $this->replacingInput = null;
        });
    }

    private static function isMigrationCommand(string $command): bool
    {
        return $command === 'migrate' || str_starts_with($command, 'migrate:') || $command === 'db:wipe';
    }

    /**
     * Point the migrator at the server and database of the application connection, as parallel tests and DB_URL change them.
     */
    private function followApplicationDatabase(string $application): bool
    {
        $connections = config('database.connections');
        $source = is_array($connections) ? ($connections[$application] ?? null) : null;
        $migrator = is_array($connections) ? ($connections[self::MIGRATOR] ?? null) : null;

        if (! is_array($source) || ! is_array($migrator) || ($source['driver'] ?? null) !== 'pgsql') {
            return false;
        }

        if ($application !== self::MIGRATOR && empty($migrator['url'])) {
            $resolved = (new ConfigurationUrlParser)->parseConfiguration($source);

            foreach (['host', 'port', 'database'] as $key) {
                config(['database.connections.'.self::MIGRATOR.".{$key}" => $resolved[$key] ?? null]);
            }
        }

        DB::purge(self::MIGRATOR);

        return true;
    }
}
