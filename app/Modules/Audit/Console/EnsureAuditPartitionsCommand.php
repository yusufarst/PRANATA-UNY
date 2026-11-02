<?php

namespace App\Modules\Audit\Console;

use App\Modules\Audit\Support\AuditPartitions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Creates the audit_events partitions of the current month and the months ahead (OPERATIONS_RUNBOOK RB-05). Safe to repeat.
 */
class EnsureAuditPartitionsCommand extends Command
{
    protected $signature = 'audit:partitions {--months='.AuditPartitions::MONTHS_AHEAD.' : Months ahead of the current one}';

    protected $description = 'Create the audit_events partitions of the current month and the months ahead';

    public function handle(): int
    {
        $months = $this->option('months');

        if (! is_string($months) || preg_match('/^\d{1,2}\z/', $months) !== 1) {
            $this->error('--months must be a whole number.');

            return self::INVALID;
        }

        foreach (AuditPartitions::ensure(DB::connection(), monthsAhead: (int) $months) as $partition) {
            $this->line($partition);
        }

        return self::SUCCESS;
    }
}
