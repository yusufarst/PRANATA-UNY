<?php

namespace Tests\Support;

use Closure;
use Illuminate\Support\Facades\DB;
use PgSql\Connection as PgConnection;
use RuntimeException;
use Throwable;

/**
 * Runs SQL as a chosen database role, and opens extra sessions for the tests that need committed data or a second connection.
 */
final class DatabaseSession
{
    /**
     * Run the callback as the role inside a savepoint that is always rolled back; give the SQLSTATE it raised, or null.
     * An exception without a SQLSTATE is not a database refusal, so it is thrown on.
     */
    public static function sqlStateAs(?string $role, Closure $callback): ?string
    {
        DB::beginTransaction();

        try {
            if ($role !== null) {
                DB::statement('set local role '.$role);
            }

            $callback();

            return null;
        } catch (Throwable $exception) {
            return self::sqlState($exception) ?? throw $exception;
        } finally {
            DB::rollBack();
        }
    }

    public static function sqlState(Throwable $exception): ?string
    {
        return preg_match('/SQLSTATE\[(\w{5})\]/', $exception->getMessage(), $match) === 1 ? $match[1] : null;
    }

    /**
     * A second Laravel connection to the test database, outside the transaction of RefreshDatabase.
     */
    public static function committedConnectionName(): string
    {
        $name = 'committed_probe';
        $default = config('database.default');

        config(["database.connections.{$name}" => [
            ...config("database.connections.{$default}"),
            'database' => DB::connection()->getDatabaseName(),
        ]]);
        DB::purge($name);

        return $name;
    }

    /**
     * A raw session to the test database, for statements that must wait on a lock while the test goes on.
     */
    public static function raw(): PgConnection
    {
        $connection = DB::connection();
        $parameters = [
            'host' => (string) $connection->getConfig('host'),
            'port' => (string) $connection->getConfig('port'),
            'dbname' => $connection->getDatabaseName(),
            'user' => (string) $connection->getConfig('username'),
            'password' => (string) $connection->getConfig('password'),
        ];

        $conninfo = implode(' ', array_map(
            fn (string $key, string $value): string => $key."='".addcslashes($value, "'\\")."'",
            array_keys($parameters),
            $parameters,
        ));

        $session = pg_connect($conninfo, PGSQL_CONNECT_FORCE_NEW);

        if ($session === false) {
            throw new RuntimeException('The raw test session could not connect.');
        }

        return $session;
    }

    /**
     * Send a statement without waiting for it.
     */
    public static function send(PgConnection $session, string $sql): void
    {
        if (! pg_send_query($session, $sql)) {
            throw new RuntimeException('The statement could not be sent.');
        }
    }

    /**
     * Wait for the statement sent last and give the SQLSTATE it raised, or null.
     */
    public static function await(PgConnection $session): ?string
    {
        $state = null;

        while (($result = pg_get_result($session)) !== false) {
            $state ??= pg_result_error_field($result, PGSQL_DIAG_SQLSTATE) ?: null;
        }

        return $state;
    }

    /**
     * Run a statement on the raw session and give the SQLSTATE it raised, or null.
     */
    public static function run(PgConnection $session, string $sql): ?string
    {
        self::send($session, $sql);

        return self::await($session);
    }

    /**
     * Wait until the backend of the session waits on a lock, as seen from a monitor session outside any transaction
     * (a transaction would keep its first snapshot of pg_stat_activity).
     */
    public static function waitUntilBlocked(PgConnection $monitor, PgConnection $session): void
    {
        $pid = pg_get_pid($session);

        for ($attempt = 0; $attempt < 200; $attempt++) {
            $result = pg_query_params($monitor, 'select wait_event_type from pg_stat_activity where pid = $1', [$pid]);

            if ($result !== false && pg_fetch_result($result, 0, 0) === 'Lock') {
                return;
            }

            usleep(25_000);
        }

        throw new RuntimeException("Session {$pid} never waited on a lock.");
    }
}
