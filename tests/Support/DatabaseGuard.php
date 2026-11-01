<?php

namespace Tests\Support;

use RuntimeException;

/**
 * Stops the test suite before it touches anything but a PRANATA test database (TEST_STRATEGY section 4).
 */
final class DatabaseGuard
{
    private const ALLOWED_DATABASE = '/^pranata_(test|e2e|perf)(_[a-z0-9_]+)?\z/';

    public static function assertSafe(string $environment, ?string $database): void
    {
        if ($environment === 'production') {
            throw new RuntimeException('The test suite must not run with APP_ENV=production.');
        }

        if ($database === null || preg_match(self::ALLOWED_DATABASE, $database) !== 1) {
            throw new RuntimeException("The test suite must not run on database [{$database}].");
        }
    }
}
