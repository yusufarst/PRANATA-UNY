<?php

use App\Modules\Platform\Support\DatabaseRoles;
use App\Modules\Platform\Support\IntegrityTriggers;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Tests\Support\DatabaseSession;

/**
 * A parent with a status and a child with a scope column, owned by pranata_owner like every table a migration creates.
 */
function createGuardedProbeTables(Connection $connection, string $prefix): void
{
    $connection->statement('set role '.DatabaseRoles::OWNER);
    $connection->statement("create table {$prefix}_parents (id bigint primary key, status varchar(20) not null)");
    $connection->statement(
        "create table {$prefix}_lines (id bigint generated always as identity primary key,"
        ." parent_id bigint not null references {$prefix}_parents (id), workspace varchar(40) not null, note text, amount integer)"
    );

    IntegrityTriggers::scopeColumns("{$prefix}_lines", ['workspace'], $connection);
    IntegrityTriggers::frozenWhenParentFinal("{$prefix}_lines", 'parent_id', "{$prefix}_parents", ['FINAL', 'CANCELLED'], mutableColumns: ['note'], connection: $connection);

    $connection->statement('reset role');
}

test('the trigger helpers refuse unknown columns and names that are not identifiers', function () {
    DB::statement('create table helper_probe (id bigint primary key, status varchar(20))');

    expect(fn () => IntegrityTriggers::scopeColumns('helper_probe', ['missing']))
        ->toThrow(InvalidArgumentException::class, 'helper_probe.missing')
        ->and(fn () => IntegrityTriggers::appendOnly('helper_probe; drop table users'))
        ->toThrow(InvalidArgumentException::class, 'not a valid identifier')
        ->and(fn () => IntegrityTriggers::frozenWhenParentFinal('helper_probe', 'id', 'helper_probe', []))
        ->toThrow(InvalidArgumentException::class, 'final status')
        ->and(fn () => IntegrityTriggers::frozenWhenParentFinal('helper_probe', 'id', 'helper_probe', ["FINAL'"]))
        ->toThrow(InvalidArgumentException::class, 'not a status code')
        ->and(fn () => IntegrityTriggers::captureManifest('helper_probe', 'id', 'helper_probe', 'helper_manifest'))
        ->toThrow(InvalidArgumentException::class, 'capture_manifest_sha256');
});

test('forbid_update_delete() refuses UPDATE and DELETE for every role', function () {
    DB::statement('set role '.DatabaseRoles::OWNER);
    DB::statement('create table append_probe (id bigint primary key, note text)');
    IntegrityTriggers::appendOnly('append_probe');
    DB::statement('reset role');
    DB::table('append_probe')->insert(['id' => 1, 'note' => 'awal']);

    expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('append_probe')->insert(['id' => 2])))->toBeNull()
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('append_probe')->update(['note' => 'ubah'])))->toBe('42501')
        ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('append_probe')->update(['note' => 'ubah'])))->toBe(IntegrityTriggers::APPEND_ONLY)
        ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('append_probe')->delete()))->toBe(IntegrityTriggers::APPEND_ONLY)
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::OWNER, fn () => DB::table('append_probe')->delete()))->toBe(IntegrityTriggers::APPEND_ONLY);
});

test('forbid_scope_change() refuses a change of a scope column, also as pranata_app', function () {
    createGuardedProbeTables(DB::connection(), 'scope_probe');
    DB::table('scope_probe_parents')->insert(['id' => 1, 'status' => 'DRAFT']);
    DB::table('scope_probe_lines')->insert(['parent_id' => 1, 'workspace' => 'unit_a', 'amount' => 1]);

    expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('scope_probe_lines')->update(['workspace' => 'unit_b'])))
        ->toBe(IntegrityTriggers::SCOPE_CHANGE)
        ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('scope_probe_lines')->update(['workspace' => 'unit_b'])))
        ->toBe(IntegrityTriggers::SCOPE_CHANGE)
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('scope_probe_lines')->update(['workspace' => 'unit_a', 'amount' => 2])))
        ->toBeNull();
});

test('forbid_change_when_parent_final() refuses changing, deleting and adding child rows of a final parent', function () {
    createGuardedProbeTables(DB::connection(), 'final_probe');
    DB::table('final_probe_parents')->insert([['id' => 1, 'status' => 'DRAFT'], ['id' => 2, 'status' => 'DRAFT']]);
    DB::table('final_probe_lines')->insert([
        ['parent_id' => 1, 'workspace' => 'unit_a', 'amount' => 1],
        ['parent_id' => 2, 'workspace' => 'unit_a', 'amount' => 1],
    ]);
    // The lines exist before their parent becomes final, as they do in the application.
    DB::table('final_probe_parents')->where('id', 2)->update(['status' => 'FINAL']);
    // Only to show that the trigger, not the missing privilege, refuses the delete of pranata_app.
    DB::statement('grant delete on final_probe_lines to '.DatabaseRoles::APP);

    $final = fn () => DB::table('final_probe_lines')->where('parent_id', 2);
    $draft = fn () => DB::table('final_probe_lines')->where('parent_id', 1);

    foreach ([DatabaseRoles::APP, null] as $role) {
        expect(DatabaseSession::sqlStateAs($role, fn () => $final()->update(['amount' => 5])))->toBe(IntegrityTriggers::PARENT_FINAL)
            ->and(DatabaseSession::sqlStateAs($role, fn () => $final()->delete()))->toBe(IntegrityTriggers::PARENT_FINAL)
            ->and(DatabaseSession::sqlStateAs($role, fn () => DB::table('final_probe_lines')->insert(['parent_id' => 2, 'workspace' => 'unit_a'])))
            ->toBe(IntegrityTriggers::PARENT_FINAL)
            // Moving a row of a draft parent under a final one is an insert into the final parent.
            ->and(DatabaseSession::sqlStateAs($role, fn () => $draft()->update(['parent_id' => 2])))->toBe(IntegrityTriggers::PARENT_FINAL)
            ->and(DatabaseSession::sqlStateAs($role, fn () => $final()->update(['note' => 'catatan boleh'])))->toBeNull()
            ->and(DatabaseSession::sqlStateAs($role, fn () => $draft()->update(['amount' => 5])))->toBeNull()
            ->and(DatabaseSession::sqlStateAs($role, fn () => DB::table('final_probe_lines')->insert(['parent_id' => 1, 'workspace' => 'unit_a'])))
            ->toBeNull();
    }

    DB::table('final_probe_parents')->where('id', 1)->update(['status' => 'CANCELLED']);

    expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $draft()->delete()))->toBe(IntegrityTriggers::PARENT_FINAL);
});

test('an insert that races the finalization of its parent waits for it and is then refused', function () {
    $probe = DB::connection(DatabaseSession::committedConnectionName());
    $finalizer = DatabaseSession::raw();
    $inserter = DatabaseSession::raw();
    $monitor = DatabaseSession::raw();

    try {
        createGuardedProbeTables($probe, 'race_probe');
        $probe->table('race_probe_parents')->insert(['id' => 1, 'status' => 'DRAFT']);

        expect(DatabaseSession::run($finalizer, "begin; update race_probe_parents set status = 'FINAL' where id = 1"))->toBeNull();

        DatabaseSession::send($inserter, 'set role '.DatabaseRoles::APP."; insert into race_probe_lines (parent_id, workspace) values (1, 'unit_a')");
        DatabaseSession::waitUntilBlocked($monitor, $inserter);

        expect(DatabaseSession::run($finalizer, 'commit'))->toBeNull()
            ->and(DatabaseSession::await($inserter))->toBe(IntegrityTriggers::PARENT_FINAL)
            ->and($probe->table('race_probe_lines')->count())->toBe(0);
    } finally {
        pg_close($finalizer);
        pg_close($inserter);
        pg_close($monitor);
        $probe->statement('drop table if exists race_probe_lines, race_probe_parents');
        DB::purge($probe->getName());
    }
});

test('verify_capture_manifest() checks the capture seal at commit', function () {
    $probe = DB::connection(DatabaseSession::committedConnectionName());
    $session = DatabaseSession::raw();
    $app = 'set local role '.DatabaseRoles::APP;

    try {
        $probe->statement('set role '.DatabaseRoles::OWNER);
        $probe->statement('create table seal_probe_parents (id bigint primary key, capture_manifest_sha256 text)');
        $probe->statement('create table seal_probe_lines (id bigint generated always as identity primary key, parent_id bigint not null references seal_probe_parents (id), qty integer not null)');
        // The fingerprint of a parent: its captured quantities in row order.
        $probe->statement(<<<'SQL'
            create function seal_probe_manifest(p_parent bigint) returns text language sql stable as $$
                select encode(sha256(convert_to(coalesce(string_agg(qty::text, ',' order by id), ''), 'UTF8')), 'hex')
                from seal_probe_lines where parent_id = p_parent
            $$
            SQL);
        IntegrityTriggers::captureManifest('seal_probe_parents', 'id', 'seal_probe_parents', 'seal_probe_manifest', $probe);
        IntegrityTriggers::captureManifest('seal_probe_lines', 'parent_id', 'seal_probe_parents', 'seal_probe_manifest', $probe);
        $probe->statement('reset role');

        $sealed = hash('sha256', '2,3');
        $wrong = hash('sha256', '9');

        // Recorded with its rows in one transaction, the seal holds.
        expect(DatabaseSession::run($session, "begin; {$app}; insert into seal_probe_parents values (1, '{$sealed}');"
            .' insert into seal_probe_lines (parent_id, qty) values (1, 2), (1, 3); commit'))->toBeNull();

        // A row added later passes its statement and is refused when the transaction commits.
        expect(DatabaseSession::run($session, "begin; {$app}; insert into seal_probe_lines (parent_id, qty) values (1, 4)"))->toBeNull()
            ->and(DatabaseSession::run($session, 'commit'))->toBe(IntegrityTriggers::CAPTURE_MANIFEST);

        // A fingerprint that does not match the rows is refused as well.
        expect(DatabaseSession::run($session, "begin; {$app}; insert into seal_probe_parents values (2, '{$wrong}');"
            .' insert into seal_probe_lines (parent_id, qty) values (2, 8); commit'))->toBe(IntegrityTriggers::CAPTURE_MANIFEST);
        DatabaseSession::run($session, 'rollback');

        expect($probe->table('seal_probe_lines')->orderBy('id')->pluck('qty')->all())->toEqual([2, 3])
            ->and($probe->table('seal_probe_parents')->pluck('id')->all())->toEqual([1]);
    } finally {
        pg_close($session);
        $probe->statement('drop table if exists seal_probe_lines, seal_probe_parents');
        $probe->statement('drop function if exists seal_probe_manifest(bigint)');
        DB::purge($probe->getName());
    }
});
