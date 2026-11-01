<?php

test('the application runs in UTC with Indonesian as the default locale', function () {
    expect(config('app.timezone'))->toBe('UTC')
        ->and(config('app.locale'))->toBe('id')
        ->and(config('app.fallback_locale'))->toBe('en');
});

test('PostgreSQL is the default database connection', function () {
    expect(config('database.default'))->toBe('pgsql');
});

// phpunit.xml and the CI job both set DB_CONNECTION, so only the source shows the fallback when it is unset.
test('no configuration falls back to another database', function () {
    foreach (['database', 'queue'] as $file) {
        expect(file_get_contents(config_path("{$file}.php")))
            ->toContain("env('DB_CONNECTION', 'pgsql')")
            ->not->toContain("env('DB_CONNECTION', 'sqlite')");
    }
});
