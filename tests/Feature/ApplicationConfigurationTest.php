<?php

test('the application runs in UTC with Indonesian as the default locale', function () {
    expect(config('app.timezone'))->toBe('UTC')
        ->and(config('app.locale'))->toBe('id')
        ->and(config('app.fallback_locale'))->toBe('en');
});

test('PostgreSQL is the default database connection', function () {
    expect(config('database.default'))->toBe('pgsql');
});
