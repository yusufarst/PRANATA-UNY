<?php

namespace App\Modules\Audit\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use InvalidArgumentException;
use RuntimeException;

/**
 * Keeps the monthly partitions of audit_events ready ahead of time (DATA_MODEL section 12, OPERATIONS_RUNBOOK RB-05).
 *
 * Months are UTC calendar months. The work is done by audit_ensure_partition(), which runs as the owner role, so the
 * scheduler can call it as pranata_app.
 */
final class AuditPartitions
{
    public const MONTHS_AHEAD = 3;

    /**
     * The SQLSTATE of audit_ensure_partition() for a month whose events are already in the default partition.
     */
    public const CAUGHT_BY_DEFAULT = 'PN005';

    /**
     * Make sure the partitions of the current month and the months ahead exist.
     *
     * @return list<string> the partition names, from the current month on
     *
     * @throws RuntimeException after the other months are made, when the default partition already holds a month
     */
    public static function ensure(Connection $connection, ?DateTimeInterface $now = null, int $monthsAhead = self::MONTHS_AHEAD): array
    {
        if ($monthsAhead < 0 || $monthsAhead > 24) {
            throw new InvalidArgumentException('The months ahead must be between 0 and 24.');
        }

        $month = CarbonImmutable::instance($now ?? CarbonImmutable::now())->utc()->startOfMonth();
        $names = [];
        $caught = [];

        for ($offset = 0; $offset <= $monthsAhead; $offset++) {
            $start = $month->addMonthsNoOverflow($offset);

            // A savepoint per month: a month the default partition holds must not stop the months after it.
            try {
                $names[] = (string) $connection->transaction(
                    fn () => $connection->scalar('select audit_ensure_partition(?::date)', [$start->toDateString()]),
                );
            } catch (QueryException $exception) {
                if (($exception->errorInfo[0] ?? null) !== self::CAUGHT_BY_DEFAULT) {
                    throw $exception;
                }

                $caught[] = $start->format('Y-m');
            }
        }

        if ($caught !== []) {
            throw new RuntimeException('audit_events_default holds events of '.implode(', ', $caught).', so those months are not created; the others are. See OPERATIONS_RUNBOOK RB-05.');
        }

        return $names;
    }
}
