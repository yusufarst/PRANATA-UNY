<?php

namespace App\Modules\Platform\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * The PostgreSQL roles of DATA_MODEL section 2: migrations own every object as pranata_owner, the application works as pranata_app.
 *
 * On a server the operator creates both roles (OPERATIONS_RUNBOOK RB-16). On a development or test server signed in as a
 * superuser, the roles are created here, because roles belong to the whole server while every new database still has to
 * let the owner build in its schema before the first migration.
 */
final class DatabaseRoles
{
    public const OWNER = 'pranata_owner';

    public const APP = 'pranata_app';

    /**
     * Make a migrator session ready: provide the roles where allowed, then work as the configured owner role.
     */
    public static function prepareMigrator(Connection $connection, ?string $role, bool $provision): void
    {
        if ($provision && (bool) $connection->scalar('select rolsuper from pg_roles where rolname = session_user')) {
            self::provision($connection);
        }

        if ($role === null || $role === '') {
            return;
        }

        // A server that lacks the role, or a login that may not take it, fails here before any migration runs.
        if ($connection->scalar('select current_user') !== $role) {
            $connection->statement('set role '.self::identifier($role));
        }

        // db:wipe, which migrate:fresh calls once the migrations table exists, disconnects; the new session has to take the role again.
        $connection->setReconnector(static function (Connection $lost) use ($role): void {
            $lost->setPdo(DB::reconnect($lost->getNameWithReadWriteType())->getRawPdo());
            $lost->statement('set role '.self::identifier($role));
        });
    }

    /**
     * Refuse to go on unless this session works as the owner role.
     */
    public static function assertOwnerSession(Connection $connection): void
    {
        $current = $connection->scalar('select current_user');

        if ($current !== self::OWNER) {
            throw new RuntimeException('Migrations must run as '.self::OWNER." through the migrator connection, not as [{$current}].");
        }
    }

    /**
     * Refuse to go on unless this session works with the privileges of pranata_app alone: no superuser, no owner.
     */
    public static function assertApplicationSession(Connection $connection): void
    {
        $allowed = (bool) $connection->scalar(
            "select not rolsuper and pg_has_role(rolname, ?, 'member') and not pg_has_role(rolname, ?, 'member') from pg_roles where rolname = current_user",
            [self::APP, self::OWNER],
        );

        if (! $allowed) {
            $current = $connection->scalar('select current_user');

            throw new RuntimeException('The application must work as a member of '.self::APP." only, not as [{$current}].");
        }
    }

    /**
     * Create the missing roles and let them use the public schema of this database.
     */
    private static function provision(Connection $connection): void
    {
        foreach ([self::OWNER, self::APP] as $role) {
            // Parallel test processes may create the same role at once; the loser keeps the role the winner made.
            $connection->statement(sprintf(<<<'SQL'
                do $$
                begin
                    if not exists (select from pg_roles where rolname = '%1$s') then
                        create role %1$s nologin;
                    end if;
                exception when duplicate_object or unique_violation then
                    null;
                end
                $$
                SQL, $role));
        }

        if (! (bool) $connection->scalar("select has_schema_privilege('".self::OWNER."', 'public', 'create')")) {
            $connection->statement('grant usage, create on schema public to '.self::OWNER);
        }

        if (! (bool) $connection->scalar("select has_schema_privilege('".self::APP."', 'public', 'usage')")) {
            $connection->statement('grant usage on schema public to '.self::APP);
        }
    }

    private static function identifier(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }
}
