<?php

namespace App\Modules\Audit\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Database\Connection;
use InvalidArgumentException;

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
     * Make sure the partitions of the current month and the months ahead exist.
     *
     * @return list<string> the partition names, from the current month on
     */
    public static function ensure(Connection $connection, ?DateTimeInterface $now = null, int $monthsAhead = self::MONTHS_AHEAD): array
    {
        if ($monthsAhead < 0 || $monthsAhead > 24) {
            throw new InvalidArgumentException('The months ahead must be between 0 and 24.');
        }

        $month = CarbonImmutable::instance($now ?? CarbonImmutable::now())->utc()->startOfMonth();
        $names = [];

        for ($offset = 0; $offset <= $monthsAhead; $offset++) {
            $names[] = (string) $connection->scalar(
                'select audit_ensure_partition(?::date)',
                [$month->addMonthsNoOverflow($offset)->toDateString()],
            );
        }

        return $names;
    }
}
