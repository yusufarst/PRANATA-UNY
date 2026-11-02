<?php

namespace App\Modules\Platform\Support;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Installs the generic integrity triggers on a table, from the migration of the Task that owns the table (DATA_MODEL section 19 rule 6).
 *
 * The trigger functions are created once by the Platform migration create_integrity_trigger_functions. Each refusal raises its
 * own SQLSTATE, so tests and callers can tell them apart from privilege and constraint errors.
 */
final class IntegrityTriggers
{
    public const APPEND_ONLY = 'PN001';

    public const SCOPE_CHANGE = 'PN002';

    public const PARENT_FINAL = 'PN003';

    public const CAPTURE_MANIFEST = 'PN004';

    /**
     * Make a table append-only: pranata_app keeps SELECT and INSERT, and a trigger refuses UPDATE and DELETE for every role.
     */
    public static function appendOnly(string $table, ?Connection $connection = null): void
    {
        $connection ??= DB::connection();
        $table = self::name($table);

        $connection->statement("revoke update, delete, truncate on table {$table} from ".DatabaseRoles::APP);
        $connection->statement(
            'create trigger '.self::name("{$table}_append_only")." before update or delete on {$table}"
            .' for each row execute function forbid_update_delete()'
        );
    }

    /**
     * Refuse any change of the scope columns after insert (DATA_MODEL section 1, DI-17).
     *
     * @param  list<string>  $columns
     */
    public static function scopeColumns(string $table, array $columns, ?Connection $connection = null): void
    {
        $connection ??= DB::connection();
        $table = self::name($table);
        self::assertColumns($connection, $table, $columns);

        $connection->statement(
            'create trigger '.self::name("{$table}_scope_guard")." before update on {$table}"
            .' for each row execute function forbid_scope_change('.self::arguments($columns).')'
        );
    }

    /**
     * Refuse inserting, changing or deleting a child row once its parent is final. The trigger locks the parent row FOR SHARE,
     * so an insert that races a finalization waits for it and then sees the final state.
     *
     * @param  list<string>  $finalStatuses
     * @param  list<string>  $mutableColumns  child columns that may still change while the parent is final
     */
    public static function frozenWhenParentFinal(
        string $table,
        string $foreignKey,
        string $parentTable,
        array $finalStatuses,
        string $statusColumn = 'status',
        array $mutableColumns = [],
        ?Connection $connection = null,
    ): void {
        $connection ??= DB::connection();
        $table = self::name($table);
        self::assertColumns($connection, $table, [$foreignKey, ...$mutableColumns]);
        self::assertColumns($connection, self::name($parentTable), ['id', $statusColumn]);

        if ($finalStatuses === []) {
            throw new InvalidArgumentException('At least one final status is required.');
        }

        foreach ($finalStatuses as $status) {
            if (preg_match('/^[A-Z][A-Z0-9_]*\z/', $status) !== 1) {
                throw new InvalidArgumentException("[{$status}] is not a status code.");
            }
        }

        $connection->statement(
            'create trigger '.self::name("{$table}_final_parent_guard")." before insert or update or delete on {$table}"
            .' for each row execute function forbid_change_when_parent_final('.self::arguments([
                $parentTable,
                $foreignKey,
                $statusColumn,
                implode(',', $finalStatuses),
                implode(',', array_map(self::name(...), $mutableColumns)),
            ]).')'
        );
    }

    /**
     * Check the capture seal of a parent at commit: the fingerprint function must still give the stored capture_manifest_sha256.
     * Install it on the parent (key column id) and on every captured child (key column = its foreign key to the parent).
     */
    public static function captureManifest(
        string $table,
        string $keyColumn,
        string $parentTable,
        string $manifestFunction,
        ?Connection $connection = null,
    ): void {
        $connection ??= DB::connection();
        $table = self::name($table);
        self::assertColumns($connection, $table, [$keyColumn]);
        self::assertColumns($connection, self::name($parentTable), ['id', 'capture_manifest_sha256']);

        if (! (bool) $connection->scalar('select to_regprocedure(?) is not null', [self::name($manifestFunction).'(bigint)'])) {
            throw new InvalidArgumentException("Function [{$manifestFunction}(bigint)] does not exist.");
        }

        $connection->statement(
            'create constraint trigger '.self::name("{$table}_capture_seal")." after insert on {$table}"
            .' deferrable initially deferred for each row execute function verify_capture_manifest('
            .self::arguments([$parentTable, $keyColumn, $manifestFunction]).')'
        );
    }

    /**
     * An unquoted PostgreSQL identifier of this project: lower case, digits and underscores, within the length limit.
     */
    private static function name(string $name): string
    {
        if (preg_match('/^[a-z][a-z0-9_]{0,62}\z/', $name) !== 1) {
            throw new InvalidArgumentException("[{$name}] is not a valid identifier.");
        }

        return $name;
    }

    /**
     * @param  list<string>  $columns
     */
    private static function assertColumns(Connection $connection, string $table, array $columns): void
    {
        foreach ($columns as $column) {
            $exists = $connection->scalar(
                'select exists (select from information_schema.columns where table_schema = current_schema() and table_name = ? and column_name = ?)',
                [$table, self::name($column)],
            );

            if (! (bool) $exists) {
                throw new InvalidArgumentException("Column [{$table}.{$column}] does not exist.");
            }
        }
    }

    /**
     * Trigger arguments are string literals; every value here is an identifier or a list of codes, so it holds no quote.
     *
     * @param  list<string>  $values
     */
    private static function arguments(array $values): string
    {
        return implode(', ', array_map(fn (string $value): string => "'{$value}'", $values));
    }
}
