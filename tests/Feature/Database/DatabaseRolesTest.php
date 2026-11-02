<?php

use App\Modules\Platform\Support\DatabaseRoles;
use Illuminate\Support\Facades\DB;
use Tests\Support\DatabaseSession;

/**
 * Whether the role holds the privilege on the table, as PostgreSQL decides it.
 */
function roleMay(string $role, string $table, string $privilege): bool
{
    return (bool) DB::scalar('select has_table_privilege(?, ?, ?)', [$role, $table, $privilege]);
}

test('migrations create every table and trigger function as pranata_owner', function () {
    $owners = DB::table('pg_tables')
        ->where('schemaname', 'public')
        ->whereIn('tablename', ['migrations', 'users', 'sessions', 'audit_events', 'audit_events_default'])
        ->pluck('tableowner', 'tablename');

    $functionOwners = collect(DB::select(
        "select proname, pg_get_userbyid(proowner) as owner from pg_proc where proname in ('forbid_update_delete', 'forbid_scope_change', 'forbid_change_when_parent_final', 'verify_capture_manifest', 'audit_ensure_partition')"
    ))->pluck('owner', 'proname');

    expect($owners)->toHaveCount(5)
        ->and($owners->unique()->values()->all())->toBe([DatabaseRoles::OWNER])
        ->and($functionOwners)->toHaveCount(5)
        ->and($functionOwners->unique()->values()->all())->toBe([DatabaseRoles::OWNER]);
});

test('pranata_app reads, adds and changes rows but deletes only from framework tables', function () {
    expect(roleMay(DatabaseRoles::APP, 'users', 'select'))->toBeTrue()
        ->and(roleMay(DatabaseRoles::APP, 'users', 'insert'))->toBeTrue()
        ->and(roleMay(DatabaseRoles::APP, 'users', 'update'))->toBeTrue()
        ->and(roleMay(DatabaseRoles::APP, 'users', 'delete'))->toBeFalse()
        ->and(roleMay(DatabaseRoles::APP, 'users', 'truncate'))->toBeFalse()
        ->and(roleMay(DatabaseRoles::APP, 'sessions', 'delete'))->toBeTrue()
        ->and(roleMay(DatabaseRoles::APP, 'migrations', 'select'))->toBeFalse();
});

test('pranata_app holds no DDL right on the schema', function () {
    expect((bool) DB::scalar("select has_schema_privilege(?, 'public', 'usage')", [DatabaseRoles::APP]))->toBeTrue()
        ->and((bool) DB::scalar("select has_schema_privilege(?, 'public', 'create')", [DatabaseRoles::APP]))->toBeFalse()
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::statement('create table app_probe (id int)')))->toBe('42501');
});

test('the application works with the privileges of pranata_app', function () {
    $state = DatabaseSession::sqlStateAs(DatabaseRoles::APP, function () {
        $id = DB::table('users')->insertGetId([
            'name' => 'Peran Aplikasi',
            'email' => 'peran.aplikasi@example.test',
            'password' => 'not-a-real-hash',
        ]);

        DB::table('users')->where('id', $id)->update(['name' => 'Peran Aplikasi Diubah']);
    });

    expect($state)->toBeNull()
        ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('users')->delete()))->toBe('42501');
});

test('the application session check accepts pranata_app alone', function () {
    $refused = [];

    foreach ([null, DatabaseRoles::OWNER, DatabaseRoles::APP] as $role) {
        DatabaseSession::sqlStateAs($role, function () use ($role, &$refused) {
            try {
                DatabaseRoles::assertApplicationSession(DB::connection());
            } catch (RuntimeException $exception) {
                $refused[] = [$role, $exception->getMessage()];
            }
        });
    }

    // The test suite signs in as a superuser.
    expect(array_column($refused, 0))->toBe([null, DatabaseRoles::OWNER])
        ->and($refused[1][1])->toBe('The application must work as a member of pranata_app only, not as [pranata_owner].');
});

test('the health check fails while the application works with more than pranata_app', function () {
    config(['database.application_role_required' => false]);
    $this->get('/up')->assertOk();

    config(['database.application_role_required' => true]);
    $this->get('/up')->assertStatus(500);
});

test('a migration refuses a session that does not work as pranata_owner', function () {
    expect(fn () => DatabaseRoles::assertOwnerSession(DB::connection()))
        ->toThrow(RuntimeException::class, 'Migrations must run as pranata_owner');

    $accepted = false;

    DatabaseSession::sqlStateAs(DatabaseRoles::OWNER, function () use (&$accepted) {
        DatabaseRoles::assertOwnerSession(DB::connection());
        $accepted = true;
    });

    expect($accepted)->toBeTrue();
});

test('the migrator connection works as the configured owner role', function () {
    config(['database.connections.migrator.database' => DB::connection()->getDatabaseName()]);
    DB::purge('migrator');

    $migrator = DB::connection('migrator');
    DatabaseRoles::prepareMigrator($migrator, DatabaseRoles::OWNER, true);

    expect($migrator->scalar('select current_user'))->toBe(DatabaseRoles::OWNER);

    // db:wipe disconnects before migrate:fresh migrates; the session after it must still work as the owner.
    $migrator->disconnect();

    expect($migrator->scalar('select current_user'))->toBe(DatabaseRoles::OWNER);

    DB::purge('migrator');
});
