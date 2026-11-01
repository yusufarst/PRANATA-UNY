<?php

use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\DatabaseGuard;

test('the test guard accepts only PRANATA test databases', function (string $database) {
    DatabaseGuard::assertSafe('testing', $database);

    expect(true)->toBeTrue();
})->with(['pranata_test', 'pranata_test_test_1', 'pranata_e2e_123', 'pranata_perf']);

test('the test guard refuses other databases', function (?string $database) {
    expect(fn () => DatabaseGuard::assertSafe('testing', $database))
        ->toThrow(RuntimeException::class);
})->with([
    'another project' => 'multi_app',
    'development database' => 'pranata_dev',
    'prefix only' => 'pranata_',
    'suffix attack' => 'other_pranata_test',
    'uppercase' => 'PRANATA_TEST',
    'trailing newline' => "pranata_test\n",
    'in memory' => ':memory:',
    'missing' => null,
]);

test('the test guard refuses production', function () {
    expect(fn () => DatabaseGuard::assertSafe('production', 'pranata_test'))
        ->toThrow(RuntimeException::class, 'APP_ENV=production');
});

test('the reset guard also allows the development database', function () {
    DatabaseResetGuard::assertAllowed('local', 'pranata_dev');

    expect(true)->toBeTrue();
});

test('the reset guard refuses other databases and production', function () {
    expect(fn () => DatabaseResetGuard::assertAllowed('local', 'other_app'))
        ->toThrow(RuntimeException::class)
        ->and(fn () => DatabaseResetGuard::assertAllowed('local', null))
        ->toThrow(RuntimeException::class)
        ->and(fn () => DatabaseResetGuard::assertAllowed('production', 'pranata_test'))
        ->toThrow(RuntimeException::class, 'production');
});

test('the reset guard recognises destructive commands and their abbreviations', function (string $command, bool $destructive) {
    expect(DatabaseResetGuard::isDestructive($command))->toBe($destructive);
})->with([
    ['migrate:fresh', true],
    ['migrate:refresh', true],
    ['migrate:reset', true],
    ['db:wipe', true],
    ['m:fr', true],
    ['db:w', true],
    ['migrate', false],
    ['migrate:status', false],
    ['db:seed', false],
    ['about', false],
    [':', false],
]);

test('migrate:fresh stops before touching a database outside the PRANATA set', function () {
    Schema::create('guard_probe', fn ($table) => $table->id());
    config(['database.connections.pgsql.database' => 'other_app']);

    expect(fn () => Artisan::call('migrate:fresh', ['--force' => true]))
        ->toThrow(RuntimeException::class, 'other_app');

    expect(DB::table('information_schema.tables')->where('table_name', 'guard_probe')->exists())->toBeTrue();
});

test('db:wipe through another connection name stops as well', function () {
    expect(fn () => Artisan::call('db:wipe', ['--database' => 'missing_connection', '--force' => true]))
        ->toThrow(RuntimeException::class);
});

test('destructive commands stop in production', function () {
    Schema::create('guard_probe', fn ($table) => $table->id());
    app()->detectEnvironment(fn () => 'production');

    expect(fn () => Artisan::call('db:wipe', ['--force' => true]))
        ->toThrow(RuntimeException::class, 'production');

    app()->detectEnvironment(fn () => 'testing');

    expect(Schema::hasTable('guard_probe'))->toBeTrue();
});
