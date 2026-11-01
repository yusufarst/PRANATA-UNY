<?php

namespace App\Modules\Platform\Support;

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
     * Determine if a destructive command may run in this environment on this database.
     */
    public static function allows(string $environment, ?string $database): bool
    {
        return $environment !== 'production'
            && $database !== null
            && preg_match(self::ALLOWED_DATABASE, $database) === 1;
    }

    /**
     * Refuse a destructive command unless the environment and database allow it.
     */
    public static function assertAllowed(string $environment, ?string $database): void
    {
        if ($environment === 'production') {
            throw new RuntimeException('Destructive database commands are forbidden in production.');
        }

        if (! self::allows($environment, $database)) {
            throw new RuntimeException("Destructive database commands are forbidden on database [{$database}].");
        }
    }
}
