<?php

namespace App\Modules\Platform\Console;

use App\Modules\Platform\Http\Middleware\EnsureIdempotentCommand;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Deletes the idempotency keys that expired (COMMAND_AND_API_CONTRACTS section 4); the scheduler runs it every hour.
 */
class PruneIdempotencyKeys extends Command
{
    protected $signature = 'platform:prune-idempotency-keys';

    protected $description = 'Delete the expired idempotency keys';

    public function handle(): int
    {
        // No request runs for 24 hours, so an expired IN_PROGRESS row belongs to a process that died and goes as well.
        $deleted = DB::table(EnsureIdempotentCommand::TABLE)->where('expires_at', '<=', now())->delete();

        $this->info("Deleted {$deleted} expired idempotency keys.");

        return self::SUCCESS;
    }
}
