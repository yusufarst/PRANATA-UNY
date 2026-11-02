<?php

namespace App\Modules\Platform\Support;

use App\Modules\Platform\Exceptions\CommandBusyException;
use App\Modules\Platform\Exceptions\CommandRetriesExhaustedException;
use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Sleep;
use PDOException;
use Throwable;

/**
 * The base of every command (COMMAND_AND_API_CONTRACTS section 2, CONCURRENCY_AND_INTEGRITY sections 1 and 6): one command is
 * one Action, and all its writes commit or roll back together in one transaction. A deadlock or serialization failure runs the
 * whole transaction again, at most twice, after a short random pause; side effects wait for the commit.
 */
abstract class Action
{
    public const MAX_RETRIES = 2;

    /**
     * The request attribute set once a command of the request committed, so a later failure does not make its key reusable.
     */
    public const COMMITTED = 'platform.command_committed';

    private const RETRYABLE = ['40P01', '40001'];

    private const LOCK_NOT_AVAILABLE = '55P03';

    /**
     * Run the work of the command in one transaction and give its result.
     *
     * @template TResult
     *
     * @param  Closure(): TResult  $work
     * @return TResult
     *
     * @throws CommandRetriesExhaustedException when every attempt failed on a deadlock or serialization failure
     * @throws CommandBusyException when a row lock did not come free within lock_timeout
     */
    protected function transaction(Closure $work): mixed
    {
        $connection = $this->connection();

        for ($attempt = 0; ; $attempt++) {
            $level = $connection->transactionLevel();
            $connection->beginTransaction();

            try {
                // Registered first, so it runs on the real commit before any callback of the work can fail, and never on a rollback.
                $connection->afterCommit(static fn () => self::markCommitted());
                $result = $work();
                $connection->commit();

                return $result;
            } catch (Throwable $exception) {
                // A failure after the commit, in an after-commit callback, finds the transaction closed and is not retried.
                if ($connection->transactionLevel() <= $level) {
                    throw $exception;
                }

                $connection->rollBack($level);
                $state = self::sqlState($exception);

                if ($state === self::LOCK_NOT_AVAILABLE) {
                    throw CommandBusyException::locked($exception);
                }

                if (! in_array($state, self::RETRYABLE, true)) {
                    throw $exception;
                }

                if ($attempt >= self::MAX_RETRIES) {
                    throw new CommandRetriesExhaustedException($exception);
                }

                Sleep::for(random_int(20, 100) * ($attempt + 1))->milliseconds();
            }
        }
    }

    /**
     * Run the callback once the transaction of the command commits, and never when it rolls back.
     */
    protected function afterCommit(Closure $callback): void
    {
        $this->connection()->afterCommit($callback);
    }

    protected function connection(): Connection
    {
        return DB::connection();
    }

    private static function markCommitted(): void
    {
        if (app()->bound('request')) {
            request()->attributes->set(self::COMMITTED, true);
        }
    }

    /**
     * The SQLSTATE of the first database error in the chain, if any.
     */
    public static function sqlState(Throwable $exception): ?string
    {
        for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
            if ($current instanceof PDOException) {
                $state = $current->errorInfo[0] ?? $current->getCode();

                if (is_string($state) && preg_match('/^[0-9A-Z]{5}\z/', $state) === 1) {
                    return $state;
                }
            }
        }

        return null;
    }
}
