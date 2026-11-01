<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('the server runs the pinned PostgreSQL major version', function () {
    $major = (int) floor((int) DB::scalar('show server_version_num') / 10000);

    expect($major)->toBeGreaterThanOrEqual(16);

    if ($expected = getenv('PRANATA_POSTGRES_MAJOR')) {
        expect($major)->toBe((int) $expected);
    }
});

test('tests run on a PRANATA test database', function () {
    expect(DB::connection()->getDatabaseName())->toMatch('/^pranata_test(_test_\d+)?\z/');
});

test('the required extensions can be created and work', function (string $extension, string $probe) {
    DB::statement("create extension if not exists {$extension}");

    expect(DB::table('pg_extension')->where('extname', $extension)->exists())->toBeTrue()
        ->and((bool) DB::scalar($probe))->toBeTrue();
})->with([
    ['pg_trgm', "select similarity('pranata', 'pranata') = 1"],
    ['ltree', "select 'unit.gudang'::ltree <@ 'unit'::ltree"],
    ['btree_gist', "select exists (select 1 from pg_opclass where opcname = 'gist_int4_ops')"],
]);

test('numeric values stay exact', function () {
    Schema::create('numeric_probe', function ($table) {
        $table->decimal('amount', 18, 2);
    });

    DB::table('numeric_probe')->insert(['amount' => '1234567890123456.78']);

    expect(DB::table('numeric_probe')->value('amount'))->toBe('1234567890123456.78')
        ->and(DB::scalar('select 0.1::numeric + 0.2::numeric = 0.3::numeric'))->toBeTrue();
});

test('timestamptz values are stored as instants and read in UTC', function () {
    expect(DB::scalar('show timezone'))->toBe('UTC');

    Schema::create('timestamptz_probe', function ($table) {
        $table->timestampTz('happened_at');
    });

    DB::table('timestamptz_probe')->insert(['happened_at' => '2026-11-01 07:00:00+07']);

    expect(DB::table('timestamptz_probe')->value('happened_at'))->toBe('2026-11-01 00:00:00+00');
});
