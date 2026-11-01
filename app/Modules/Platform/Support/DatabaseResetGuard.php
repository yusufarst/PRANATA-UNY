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
    private const COMMANDS = ['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'db:wipe'];

    private const ALLOWED_DATABASE = '/^pranata_(dev|test|e2e|perf)(_[a-z0-9_]+)?\z/';

    /**
     * Determine if the typed command may resolve to a destructive command.
     *
     * Artisan accepts abbreviations such as "m:fr", so each part is compared as a prefix.
     */
    public static function isDestructive(string $command): bool
    {
        $typed = explode(':', $command);

        if (count($typed) !== 2 || in_array('', $typed, true)) {
            return false;
        }

        foreach (self::COMMANDS as $destructive) {
            [$namespace, $name] = explode(':', $destructive);

            if (str_starts_with($namespace, $typed[0]) && str_starts_with($name, $typed[1])) {
                return true;
            }
        }

        return false;
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
