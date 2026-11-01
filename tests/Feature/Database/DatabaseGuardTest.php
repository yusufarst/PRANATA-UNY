<?php

use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Console\Events\CommandStarting;
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
function startCommand(string $command, array $parameters = []): void
{
    event(new CommandStarting($command, new ArrayInput($parameters), new NullOutput));
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

test('the reset guard listens for a destructive command in production', function () {
    app()->detectEnvironment(fn () => 'production');

    expect(fn () => startCommand('db:wipe'))
        ->toThrow(RuntimeException::class, 'forbidden in production');

    app()->detectEnvironment(fn () => 'testing');
});

test('the reset guard lets other commands and allowed databases through', function () {
    startCommand('migrate:status');
    startCommand('migrate:fresh');

    expect(true)->toBeTrue();
});

// Laravel fires the event only outside APP_ENV=testing, so these run the artisan script itself.
test('artisan migrate:fresh stops on a foreign database', function () {
    $result = Process::path(base_path())
        ->env(['APP_ENV' => 'local', 'DB_DATABASE' => 'other_app'])
        ->run([PHP_BINARY, 'artisan', 'migrate:fresh', '--force']);

    expect($result->output().$result->errorOutput())->toContain('forbidden on database [other_app]')
        ->and($result->failed())->toBeTrue()
        ->and(DB::table('pg_database')->where('datname', 'other_app')->exists())->toBeFalse();
});

test('artisan migrate:fresh also stops on a foreign database under APP_ENV=testing', function () {
    $result = Process::path(base_path())
        ->env(['APP_ENV' => 'testing', 'DB_DATABASE' => 'other_app'])
        ->run([PHP_BINARY, 'artisan', 'migrate:fresh', '--force']);

    expect($result->output().$result->errorOutput())->toContain('prohibited')
        ->and($result->failed())->toBeTrue()
        ->and(DB::table('pg_database')->where('datname', 'other_app')->exists())->toBeFalse();
});

test('artisan db:wipe stops in production', function () {
    $result = Process::path(base_path())
        ->env(['APP_ENV' => 'production'])
        ->run([PHP_BINARY, 'artisan', 'db:wipe', '--force']);

    expect($result->output().$result->errorOutput())->toContain('forbidden in production')
        ->and($result->failed())->toBeTrue();
});
