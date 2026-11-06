<?php

use App\Modules\Files\Support\FileGuards;
use App\Modules\Platform\Support\DatabaseRoles;
use App\Modules\Platform\Support\IntegrityTriggers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\DatabaseSession;

/*
 * DI-33 at the database (TASK-016 TAC-05, TAC-06): the column privileges of pranata_app, guard_file_object_change() and
 * guard_file_link_change() for every role, the registry of sealed owner types, and the seal of a link to a sealed owner,
 * including from a second connection while the recording transaction is open.
 *
 * The sealed owner of V1 (proc_submissions) arrives with TASK-033, so a probe owner table with a probe seal function stands
 * in for it here, registered as the migration of that owner would register it.
 */

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function fileObjectRow(array $overrides = []): array
{
    return [
        'public_id' => strtolower((string) Str::ulid()),
        'disk' => 'local',
        'path' => '2026/11/'.bin2hex(random_bytes(16)),
        'original_name' => 'sk.pdf',
        'mime_type' => 'application/pdf',
        'size_bytes' => 10,
        'sha256' => hash('sha256', 'sk'),
        'classification' => 'INTERNAL',
        ...$overrides,
    ];
}

function insertFileObject(string $content = 'sk'): int
{
    return DB::table('file_objects')->insertGetId(fileObjectRow(['sha256' => hash('sha256', $content), 'size_bytes' => strlen($content)]));
}

/**
 * A link to an owner type that is not sealed; the row it names need not exist.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function fileLinkRow(int $fileId, array $overrides = []): array
{
    return ['file_id' => $fileId, 'owner_type' => 'org_appointments', 'owner_id' => 1, 'purpose' => 'DECREE', ...$overrides];
}

function columnPrivilege(string $table, string $column, string $privilege): bool
{
    return (bool) DB::scalar('select has_column_privilege(?, ?, ?, ?)', [DatabaseRoles::APP, $table, $column, $privilege]);
}

function tablePrivilege(string $table, string $privilege): bool
{
    return (bool) DB::scalar('select has_table_privilege(?, ?, ?)', [DatabaseRoles::APP, $table, $privilege]);
}

describe('the privileges of pranata_app (DATA_MODEL section 2)', function () {
    test('file_objects: read and add, update only the classification and the scan status, never delete', function () {
        expect(tablePrivilege('file_objects', 'select'))->toBeTrue()
            ->and(tablePrivilege('file_objects', 'insert'))->toBeTrue()
            ->and(tablePrivilege('file_objects', 'delete'))->toBeFalse()
            ->and(tablePrivilege('file_objects', 'truncate'))->toBeFalse()
            ->and(columnPrivilege('file_objects', 'classification', 'update'))->toBeTrue()
            ->and(columnPrivilege('file_objects', 'scan_status', 'update'))->toBeTrue();

        foreach (['public_id', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'sha256', 'uploaded_by', 'uploaded_at'] as $column) {
            expect(columnPrivilege('file_objects', $column, 'update'))->toBeFalse("{$column} is updatable by pranata_app");
        }
    });

    test('file_links: read and add, update only the label and the release, never delete', function () {
        expect(tablePrivilege('file_links', 'select'))->toBeTrue()
            ->and(tablePrivilege('file_links', 'insert'))->toBeTrue()
            ->and(tablePrivilege('file_links', 'delete'))->toBeFalse()
            ->and(columnPrivilege('file_links', 'label', 'update'))->toBeTrue()
            ->and(columnPrivilege('file_links', 'unlinked_by', 'update'))->toBeTrue()
            ->and(columnPrivilege('file_links', 'unlinked_at', 'update'))->toBeTrue();

        foreach (['file_id', 'owner_type', 'owner_id', 'purpose', 'linked_by', 'linked_at'] as $column) {
            expect(columnPrivilege('file_links', $column, 'update'))->toBeFalse("{$column} is updatable by pranata_app");
        }
    });

    test('file_sealed_owner_types: read only; a superuser cannot change or delete a registration either', function () {
        expect(tablePrivilege('file_sealed_owner_types', 'select'))->toBeTrue()
            ->and(tablePrivilege('file_sealed_owner_types', 'insert'))->toBeFalse()
            ->and(tablePrivilege('file_sealed_owner_types', 'update'))->toBeFalse()
            ->and(tablePrivilege('file_sealed_owner_types', 'delete'))->toBeFalse();

        DB::statement('set local role '.DatabaseRoles::OWNER);
        DB::table('file_sealed_owner_types')->insert(['owner_type' => 'org_appointments', 'seal_function' => 'no_such_function', 'registered_in' => 'FileGuardsTest']);
        DB::statement('reset role');

        expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('file_sealed_owner_types')->insert(['owner_type' => 'users', 'seal_function' => 'x', 'registered_in' => 'x'])))->toBe('42501')
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('file_sealed_owner_types')->update(['seal_function' => 'other'])))->toBe(IntegrityTriggers::APPEND_ONLY)
            ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('file_sealed_owner_types')->delete()))->toBe(IntegrityTriggers::APPEND_ONLY);
    });
});

describe('guard_file_object_change() (TAC-05)', function () {
    test('the content identity never changes, for pranata_app by its privileges and for every other role by the trigger', function () {
        $id = insertFileObject();
        $changes = [
            'public_id' => strtolower((string) Str::ulid()),
            'disk' => 'other',
            'path' => '2026/11/'.bin2hex(random_bytes(16)),
            'original_name' => 'lain.pdf',
            'mime_type' => 'image/png',
            'size_bytes' => 11,
            'sha256' => hash('sha256', 'lain'),
            // The trigger fires before the foreign key would, so a user that does not exist still proves the refusal.
            'uploaded_by' => 1,
            'uploaded_at' => '2020-01-01 00:00:00+00',
        ];

        foreach ($changes as $column => $value) {
            expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('file_objects')->where('id', $id)->update([$column => $value])))
                ->toBe('42501', "pranata_app changed {$column}")
                ->and(DatabaseSession::sqlStateAs(null, fn () => DB::table('file_objects')->where('id', $id)->update([$column => $value])))
                ->toBe(FileGuards::OBJECT_CHANGE, "a superuser changed {$column}")
                ->and(DatabaseSession::sqlStateAs(DatabaseRoles::OWNER, fn () => DB::table('file_objects')->where('id', $id)->update([$column => $value])))
                ->toBe(FileGuards::OBJECT_CHANGE, "the owner changed {$column}");
        }

        expect(DatabaseSession::sqlStateAs(null, fn () => DB::table('file_objects')->where('id', $id)->delete()))->toBe(FileGuards::OBJECT_CHANGE)
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('file_objects')->where('id', $id)->delete()))->toBe('42501');
    });

    test('the classification only rises and a scan result never goes back to NOT_SCANNED; a new scan result may replace one', function () {
        $id = insertFileObject();
        $update = fn (array $values) => DB::table('file_objects')->where('id', $id)->update($values);

        expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $update(['classification' => 'RESTRICTED'])))->toBeNull()
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $update(['classification' => 'INTERNAL', 'scan_status' => 'NOT_SCANNED'])))->toBeNull();

        $update(['classification' => 'CONFIDENTIAL']);

        foreach ([DatabaseRoles::APP, null] as $role) {
            expect(DatabaseSession::sqlStateAs($role, fn () => $update(['classification' => 'INTERNAL'])))->toBe(FileGuards::OBJECT_CHANGE)
                ->and(DatabaseSession::sqlStateAs($role, fn () => $update(['classification' => 'RESTRICTED'])))->toBeNull()
                ->and(DatabaseSession::sqlStateAs($role, fn () => $update(['scan_status' => 'CLEAN'])))->toBeNull();
        }

        $update(['scan_status' => 'CLEAN']);

        foreach ([DatabaseRoles::APP, null] as $role) {
            expect(DatabaseSession::sqlStateAs($role, fn () => $update(['scan_status' => 'NOT_SCANNED'])))->toBe(FileGuards::OBJECT_CHANGE)
                ->and(DatabaseSession::sqlStateAs($role, fn () => $update(['scan_status' => 'INFECTED'])))->toBeNull()
                ->and(DatabaseSession::sqlStateAs($role, fn () => $update(['scan_status' => 'ERROR'])))->toBeNull();
        }

        expect(DB::table('file_objects')->where('id', $id)->value('sha256'))->toBe(hash('sha256', 'sk'));
    });

    test('the table refuses a malformed fingerprint, a negative size and a path that climbs out of the disk', function () {
        $insert = fn (array $overrides) => DB::table('file_objects')->insert(fileObjectRow($overrides));

        expect(DatabaseSession::sqlStateAs(null, fn () => $insert(['sha256' => strtoupper(hash('sha256', 'x'))])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => $insert(['sha256' => 'abc'])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => $insert(['size_bytes' => -1])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => $insert(['path' => '../etc/passwd'])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => $insert(['path' => '/etc/passwd'])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(null, fn () => $insert(['classification' => 'PUBLIC'])))->toBe('23514')
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $insert([])))->toBeNull();
    });
});

describe('guard_file_link_change() on a link that is not sealed (TAC-06)', function () {
    test('a link is created active, to a table that exists, with the database clock', function () {
        $fileId = insertFileObject();
        $insert = fn (array $overrides) => DB::table('file_links')->insert(fileLinkRow($fileId, $overrides));

        // The trigger runs before the CHECK constraints, so every variant of the table name is refused by it.
        expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $insert(['owner_type' => 'no_such_table'])))->toBe(FileGuards::LINK_CHANGE)
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $insert(['owner_type' => 'Org_Appointments'])))->toBe(FileGuards::LINK_CHANGE)
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $insert(['owner_type' => 'org appointments'])))->toBe(FileGuards::LINK_CHANGE)
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $insert(['owner_type' => 'org_appointments '])))->toBe(FileGuards::LINK_CHANGE)
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $insert(['unlinked_by' => null, 'unlinked_at' => '2026-01-01 00:00:00+00'])))->toBe(FileGuards::LINK_CHANGE)
            ->and(DatabaseSession::sqlStateAs(null, fn () => $insert(['unlinked_at' => '2026-01-01 00:00:00+00', 'unlinked_by' => 1])))->toBe(FileGuards::LINK_CHANGE);

        DB::statement('set local role '.DatabaseRoles::APP);
        $linkId = DB::table('file_links')->insertGetId(fileLinkRow($fileId, ['linked_at' => '2000-01-01 00:00:00+00']));
        DB::statement('reset role');

        $link = DB::table('file_links')->where('id', $linkId)->first();

        expect($link->unlinked_at)->toBeNull()
            ->and(strtotime((string) $link->linked_at))->toBeGreaterThan(strtotime('2026-01-01'));
    });

    test('the file, owner, purpose and linking never change; the label may', function () {
        $fileId = insertFileObject();
        $otherFile = insertFileObject('lain');
        $linkId = DB::table('file_links')->insertGetId(fileLinkRow($fileId));
        $update = fn (array $values) => DB::table('file_links')->where('id', $linkId)->update($values);
        $changes = [
            'file_id' => $otherFile,
            'owner_type' => 'process_assignments',
            'owner_id' => 2,
            'purpose' => 'OTHER',
            'linked_by' => 1,
            'linked_at' => '2000-01-01 00:00:00+00',
        ];

        foreach ($changes as $column => $value) {
            expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $update([$column => $value])))->toBe('42501', "pranata_app changed {$column}")
                ->and(DatabaseSession::sqlStateAs(null, fn () => $update([$column => $value])))->toBe(FileGuards::LINK_CHANGE, "a superuser changed {$column}");
        }

        expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $update(['label' => 'SK 12/2026'])))->toBeNull()
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => DB::table('file_links')->where('id', $linkId)->delete()))->toBe('42501');
    });

    test('a release happens once, with the database clock and who released; linking again or changing the trace is refused', function () {
        $fileId = insertFileObject();
        $linkId = DB::table('file_links')->insertGetId(fileLinkRow($fileId));
        $userId = DB::table('users')->insertGetId([
            'public_id' => strtolower((string) Str::ulid()),
            'role_group' => 'INTERNAL',
            'username' => 'pelepas',
            'name' => 'Pelepas Tautan',
            'email' => 'pelepas@example.test',
            'password' => 'not-a-real-hash',
        ]);
        $update = fn (array $values) => DB::table('file_links')->where('id', $linkId)->update($values);

        // Half a release is refused: a time without who released by the trigger, who released without a time by the table.
        expect(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $update(['unlinked_at' => now()])))->toBe(FileGuards::LINK_CHANGE)
            ->and(DatabaseSession::sqlStateAs(DatabaseRoles::APP, fn () => $update(['unlinked_by' => $userId])))->toBe('23514');

        DB::statement('set local role '.DatabaseRoles::APP);
        $update(['unlinked_by' => $userId, 'unlinked_at' => '2000-01-01 00:00:00+00']);
        DB::statement('reset role');

        $released = DB::table('file_links')->where('id', $linkId)->first();

        expect($released->unlinked_by)->toBe($userId)
            ->and(strtotime((string) $released->unlinked_at))->toBeGreaterThan(strtotime('2026-01-01'));

        foreach ([DatabaseRoles::APP, null] as $role) {
            expect(DatabaseSession::sqlStateAs($role, fn () => $update(['unlinked_by' => null, 'unlinked_at' => null])))->toBe(FileGuards::LINK_CHANGE)
                ->and(DatabaseSession::sqlStateAs($role, fn () => $update(['unlinked_at' => '2030-01-01 00:00:00+00'])))->toBe(FileGuards::LINK_CHANGE)
                ->and(DatabaseSession::sqlStateAs($role, fn () => $update(['label' => 'diubah sesudah lepas'])))->toBe(FileGuards::LINK_CHANGE);
        }
    });
});

describe('a link to a sealed owner (TAC-06, DI-33)', function () {
    test('is created only in the transaction that records its owner, is never changed, released or deleted, and a second connection sees none of it', function () {
        $probe = DB::connection(DatabaseSession::committedConnectionName());
        $recorder = DatabaseSession::raw();
        $other = DatabaseSession::raw();
        $app = 'set local role '.DatabaseRoles::APP;
        $fileIds = [];

        try {
            // The probe owner and its seal function, as the migration of a sealed owner would create and register them.
            $probe->statement('set role '.DatabaseRoles::OWNER);
            $probe->statement('create table seal_probe_owners (id bigint primary key, capture_manifest_sha256 char(64) not null)');
            $probe->statement('grant select, insert on seal_probe_owners to '.DatabaseRoles::APP);
            $probe->statement(<<<'SQL'
                create function seal_probe_manifest(p_owner bigint) returns text language sql stable as $$
                    select encode(sha256(convert_to(coalesce(string_agg(f.sha256, ',' order by l.id), ''), 'UTF8')), 'hex')
                    from file_links l join file_objects f on f.id = l.file_id
                    where l.owner_type = 'seal_probe_owners' and l.owner_id = p_owner and l.unlinked_at is null
                $$
                SQL);
            $probe->table('file_sealed_owner_types')->insert(['owner_type' => 'seal_probe_owners', 'seal_function' => 'seal_probe_manifest', 'registered_in' => 'FileGuardsTest']);
            $probe->statement('reset role');

            foreach (['A', 'B', 'C'] as $content) {
                $fileIds[$content] = $probe->table('file_objects')->insertGetId(fileObjectRow(['sha256' => hash('sha256', $content), 'size_bytes' => 1]));
            }

            $link = fn (string $content, int $owner): string => "insert into file_links (file_id, owner_type, owner_id, purpose) values ({$fileIds[$content]}, 'seal_probe_owners', {$owner}, 'OFFER')";
            $seal = fn (string ...$contents): string => hash('sha256', implode(',', array_map(fn (string $content) => hash('sha256', $content), $contents)));

            // Recorded with its link in one transaction as pranata_app: the seal holds at commit.
            expect(DatabaseSession::run($recorder, "begin; {$app}; insert into seal_probe_owners values (1, '{$seal('A')}'); {$link('A', 1)}; commit"))->toBeNull();

            // A link added after the recording passes its statement and is refused when the transaction commits.
            expect(DatabaseSession::run($recorder, "begin; {$app}; {$link('B', 1)}"))->toBeNull()
                ->and(DatabaseSession::run($recorder, 'commit'))->toBe(FileGuards::CAPTURE_MANIFEST);

            // A link to a sealed owner that does not exist is refused at once.
            expect(DatabaseSession::run($recorder, "begin; {$app}; {$link('B', 999)}"))->toBe(FileGuards::LINK_CHANGE);
            DatabaseSession::run($recorder, 'rollback');

            // Nothing of the sealed link changes: not its label, not a release, not its owner, and it is not deleted;
            // as pranata_app and as a superuser.
            $sealedId = (int) $probe->table('file_links')->where('owner_type', 'seal_probe_owners')->where('owner_id', 1)->value('id');

            foreach ([$app, ''] as $role) {
                foreach ([
                    "update file_links set label = 'x' where id = {$sealedId}" => FileGuards::LINK_CHANGE,
                    "update file_links set unlinked_by = null, unlinked_at = now() where id = {$sealedId}" => FileGuards::LINK_CHANGE,
                    // A privilege pranata_app lacks refuses before the trigger can; a superuser reaches the trigger.
                    "update file_links set owner_id = 2 where id = {$sealedId}" => $role === '' ? FileGuards::LINK_CHANGE : '42501',
                    "delete from file_links where id = {$sealedId}" => $role === '' ? FileGuards::LINK_CHANGE : '42501',
                ] as $statement => $expected) {
                    $state = DatabaseSession::run($other, "begin; {$role}; {$statement}");
                    DatabaseSession::run($other, 'rollback');

                    expect($state)->toBe($expected, $statement);
                }
            }

            // While the recording transaction of owner 2 is open, a second connection cannot see its link: releasing it changes
            // nothing, and linking to owner 2 is refused because the row is not visible yet. After the commit the link is sealed.
            expect(DatabaseSession::run($recorder, "begin; {$app}; insert into seal_probe_owners values (2, '{$seal('B')}'); {$link('B', 2)}"))->toBeNull();

            $affected = $probe->table('file_links')->where('owner_type', 'seal_probe_owners')->where('owner_id', 2)->update(['unlinked_by' => null, 'unlinked_at' => now()]);

            expect($affected)->toBe(0)
                ->and(DatabaseSession::run($other, "begin; {$app}; {$link('C', 2)}"))->toBe(FileGuards::LINK_CHANGE);
            DatabaseSession::run($other, 'rollback');

            expect(DatabaseSession::run($recorder, 'commit'))->toBeNull();

            $afterCommit = null;

            try {
                $probe->table('file_links')->where('owner_type', 'seal_probe_owners')->where('owner_id', 2)->update(['label' => 'x']);
            } catch (Throwable $exception) {
                $afterCommit = DatabaseSession::sqlState($exception);
            }

            expect($afterCommit)->toBe(FileGuards::LINK_CHANGE)
                ->and($probe->table('file_links')->where('owner_type', 'seal_probe_owners')->whereNull('unlinked_at')->count())->toBe(2)
                ->and($probe->table('file_links')->where('owner_type', 'seal_probe_owners')->where('file_id', $fileIds['C'])->exists())->toBeFalse();
        } finally {
            pg_close($recorder);
            pg_close($other);

            // Test cleanup only: the guards refuse a delete for every role, so they are switched off for these probe rows.
            $probe->statement('alter table file_links disable trigger file_links_guard');
            $probe->statement('alter table file_objects disable trigger file_objects_guard');
            $probe->statement('alter table file_sealed_owner_types disable trigger file_sealed_owner_types_append_only');
            $probe->table('file_links')->where('owner_type', 'seal_probe_owners')->delete();
            $probe->table('file_objects')->whereIn('id', array_values($fileIds))->delete();
            $probe->table('file_sealed_owner_types')->where('owner_type', 'seal_probe_owners')->delete();
            $probe->statement('alter table file_links enable trigger file_links_guard');
            $probe->statement('alter table file_objects enable trigger file_objects_guard');
            $probe->statement('alter table file_sealed_owner_types enable trigger file_sealed_owner_types_append_only');
            $probe->statement('drop table if exists seal_probe_owners');
            $probe->statement('drop function if exists seal_probe_manifest(bigint)');
            DB::purge($probe->getName());
        }
    });
});
