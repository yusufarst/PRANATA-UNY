<?php

use App\Models\User;
use Database\Seeders\ScenarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function e2eDatabaseExists(string $name): bool
{
    return DB::selectOne('select 1 as found from pg_database where datname = ?', [$name]) !== null;
}

test('a run database is created and dropped', function () {
    $name = 'pranata_e2e_pest_'.getmypid();

    // CREATE DATABASE refuses to run inside the transaction of RefreshDatabase.
    DB::commit();

    try {
        $this->artisan('e2e:database', ['action' => 'create', 'name' => $name])->assertSuccessful();
        expect(e2eDatabaseExists($name))->toBeTrue();

        $this->artisan('e2e:database', ['action' => 'drop', 'name' => $name])->assertSuccessful();
        expect(e2eDatabaseExists($name))->toBeFalse();
    } finally {
        DB::statement('drop database if exists "'.$name.'"');
        DB::beginTransaction();
    }
});

test('the command refuses any other database', function (string $name) {
    expect(fn () => $this->artisan('e2e:database', ['action' => 'drop', 'name' => $name])->run())
        ->toThrow(RuntimeException::class, 'is not an E2E database');
})->with(['pranata_test', 'other_app', 'pranata_e2e_', 'pranata_e2e_x"; drop database pranata_test; --', 'PRANATA_E2E_X']);

test('the command refuses an unknown action', function () {
    expect(fn () => $this->artisan('e2e:database', ['action' => 'wipe', 'name' => 'pranata_e2e_x'])->run())
        ->toThrow(RuntimeException::class, 'Unknown action');
});

test('the command refuses production', function () {
    app()->detectEnvironment(fn () => 'production');

    expect(fn () => $this->artisan('e2e:database', ['action' => 'create', 'name' => 'pranata_e2e_x'])->run())
        ->toThrow(RuntimeException::class, 'forbidden in production');
});

test('the seeder makes one user per profile and a repeat collides with nothing', function () {
    $this->seed(ScenarioSeeder::class);
    $this->seed(ScenarioSeeder::class);

    foreach (ScenarioSeeder::PROFILES as $profile) {
        expect(User::query()->where('email', "{$profile}.local@e2e.pranata.test")->count())->toBe(1);
    }
});

test('a second run on the same database gets its own identities', function () {
    $this->seed(ScenarioSeeder::class);

    putenv('E2E_RUN_ID=second');

    try {
        $this->seed(ScenarioSeeder::class);
    } finally {
        putenv('E2E_RUN_ID');
    }

    expect(User::query()->where('email', 'like', '%@e2e.pranata.test')->count())
        ->toBe(2 * count(ScenarioSeeder::PROFILES));
});

test('the seeder refuses production', function () {
    app()->detectEnvironment(fn () => 'production');

    // Without --force, db:seed stops at its production prompt and the seeder never starts.
    expect(fn () => $this->artisan('db:seed', ['--class' => ScenarioSeeder::class, '--force' => true])->run())
        ->toThrow(RuntimeException::class, 'forbidden in production');
});
