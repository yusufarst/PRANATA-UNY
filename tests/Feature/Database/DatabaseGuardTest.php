<?php

use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
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

test('the reset guard covers every command that drops or empties tables', function () {
    expect(DatabaseResetGuard::COMMANDS)
        ->toEqualCanonicalizing(['migrate:fresh', 'migrate:refresh', 'migrate:reset', 'db:wipe']);
});

/**
 * Dispatch the event Artisan fires before a command, without running the command.
 *
 * @param  array<string, string>  $parameters
 */
function startCommand(string $command, array $parameters = []): ArrayInput
{
    event(new CommandStarting($command, $input = new ArrayInput($parameters), new NullOutput));

    return $input;
}

test('the reset guard listens for a destructive command on a foreign database', function () {
    $original = config('database.connections.pgsql.database');
    config(['database.connections.pgsql.database' => 'other_app']);

    expect(fn () => startCommand('migrate:fresh'))
        ->toThrow(RuntimeException::class, 'forbidden on database [other_app]');

    config(['database.connections.pgsql.database' => $original]);
});

test('the reset guard listens for a destructive command through another connection', function () {
    expect(fn () => startCommand('db:wipe', ['--database' => 'missing_connection']))
        ->toThrow(RuntimeException::class, 'forbidden on database []');
});

test('the reset guard checks the database the migrator really reaches', function () {
    config(['database.connections.migrator.url' => 'postgresql://pranata@127.0.0.1:5432/other_app']);

    try {
        expect(fn () => startCommand('db:wipe'))
            ->toThrow(RuntimeException::class, 'forbidden on database [other_app]')
            ->and(config('database.default'))->toBe('pgsql');
    } finally {
        config(['database.connections.migrator.url' => null]);
        DB::purge('migrator');
    }
});

test('the reset guard listens for a destructive command in production', function () {
    app()->detectEnvironment(fn () => 'production');

    expect(fn () => startCommand('db:wipe'))
        ->toThrow(RuntimeException::class, 'forbidden in production');

    app()->detectEnvironment(fn () => 'testing');
});

test('the reset guard lets other commands and allowed databases through', function () {
    foreach (['migrate:status', 'migrate:fresh'] as $command) {
        $input = startCommand($command);
        event(new CommandFinished($command, $input, new NullOutput, 0));
    }

    expect(config('database.default'))->toBe('pgsql');
});

test('a command called by a migration command keeps the migrator until the caller finishes', function () {
    $input = startCommand('migrate:fresh');

    // migrate:fresh calls db:wipe and then migrate; the end of db:wipe must not hand migrate back to pgsql.
    event(new CommandFinished('db:wipe', new ArrayInput([]), new NullOutput, 0));

    expect(config('database.default'))->toBe('migrator');

    event(new CommandFinished('migrate:fresh', $input, new NullOutput, 0));

    expect(config('database.default'))->toBe('pgsql');
});

test('the database name is read after DB_URL is applied', function () {
    expect(DatabaseResetGuard::databaseName([
        'driver' => 'pgsql',
        'url' => 'postgresql://pranata@127.0.0.1:5432/other_app',
        'database' => 'pranata_test',
    ]))->toBe('other_app');
});

test('only a configured PostgreSQL connection has a database name', function () {
    expect(DatabaseResetGuard::databaseName(null))->toBeNull()
        ->and(DatabaseResetGuard::databaseName(['driver' => 'sqlite', 'database' => 'pranata_test']))->toBeNull()
        ->and(DatabaseResetGuard::databaseName(['driver' => 'pgsql', 'database' => 'pranata_test']))->toBe('pranata_test');
});

test('a command called from code stops under APP_ENV=testing as well', function () {
    expect(fn () => Artisan::call('db:wipe', ['--database' => 'sqlite', '--force' => true]))
        ->toThrow(RuntimeException::class, 'forbidden on database');
});

// These run the artisan script itself, with an environment of their own.
test('artisan migrate:fresh stops on a foreign database', function (string $environment, array $variables, array $options, string $message) {
    $result = Process::path(base_path())
        ->env(['APP_ENV' => $environment, ...$variables])
        ->run([PHP_BINARY, 'artisan', 'migrate:fresh', '--force', ...$options]);

    expect($result->output().$result->errorOutput())->toContain($message)
        ->and($result->failed())->toBeTrue()
        ->and(DB::table('pg_database')->where('datname', 'other_app')->exists())->toBeFalse();
})->with([
    'foreign name, local' => ['local', ['DB_DATABASE' => 'other_app'], [], 'forbidden on database [other_app]'],
    'foreign name, testing' => ['testing', ['DB_DATABASE' => 'other_app'], [], 'forbidden on database [other_app]'],
    'foreign URL, local' => ['local', ['DB_DATABASE' => 'pranata_test', 'DB_URL' => 'postgresql://pranata@127.0.0.1:5432/other_app'], [], 'forbidden on database [other_app]'],
    'foreign URL, testing' => ['testing', ['DB_DATABASE' => 'pranata_test', 'DB_URL' => 'postgresql://pranata@127.0.0.1:5432/other_app'], [], 'forbidden on database [other_app]'],
    'foreign migrator URL, testing' => ['testing', ['DB_DATABASE' => 'pranata_test', 'DB_MIGRATOR_URL' => 'postgresql://pranata@127.0.0.1:5432/other_app'], [], 'forbidden on database [other_app]'],
    'other driver, local' => ['local', [], ['--database=sqlite'], 'forbidden on database'],
    'other driver, testing' => ['testing', [], ['--database=sqlite'], 'forbidden on database'],
    'unknown connection, testing' => ['testing', [], ['--database=missing_connection'], 'forbidden on database'],
]);

test('artisan db:wipe stops in production', function () {
    $result = Process::path(base_path())
        ->env(['APP_ENV' => 'production'])
        ->run([PHP_BINARY, 'artisan', 'db:wipe', '--force']);

    expect($result->output().$result->errorOutput())->toContain('forbidden in production')
        ->and($result->failed())->toBeTrue();
});
