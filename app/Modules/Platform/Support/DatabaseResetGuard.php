<?php

namespace App\Modules\Platform\Support;

use Illuminate\Support\ConfigurationUrlParser;
use RuntimeException;

/**
 * Stops destructive database commands outside the PRANATA databases and in production.
 */
final class DatabaseResetGuard
{
    /**
     * The commands that drop or empty tables.
     *
     * @var list<string>
     */
    public const COMMANDS = ['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'db:wipe'];

    private const ALLOWED_DATABASE = '/^pranata_(dev|test|e2e|perf)(_[a-z0-9_]+)?\z/';

    /**
     * Get the PostgreSQL database a connection really uses, after DB_URL overrides are applied.
     *
     * @param  array<string, mixed>|null  $connection
     */
    public static function databaseName(?array $connection): ?string
    {
        if ($connection === null) {
            return null;
        }

        $resolved = (new ConfigurationUrlParser)->parseConfiguration($connection);

        if (($resolved['driver'] ?? null) !== 'pgsql' || ! is_string($resolved['database'] ?? null)) {
            return null;
        }

        return $resolved['database'];
    }

    /**
     * Refuse a destructive command unless the environment and database allow it.
     */
    public static function assertAllowed(string $environment, ?string $database): void
    {
        if ($environment === 'production') {
            throw new RuntimeException('Destructive database commands are forbidden in production.');
        }

        if ($database === null || preg_match(self::ALLOWED_DATABASE, $database) !== 1) {
            throw new RuntimeException("Destructive database commands are forbidden on database [{$database}].");
        }
    }
}
