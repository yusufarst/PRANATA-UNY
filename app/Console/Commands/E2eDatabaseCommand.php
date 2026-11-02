<?php

namespace App\Console\Commands;

use App\Modules\Platform\Support\DatabaseResetGuard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Creates and drops the database of one E2E run, through the server of the current connection.
 */
class E2eDatabaseCommand extends Command
{
    protected $signature = 'e2e:database {action : create or drop} {name : pranata_e2e_<run>}';

    protected $description = 'Create or drop the database of one E2E run';

    public function handle(): int
    {
        $action = (string) $this->argument('action');
        $name = (string) $this->argument('name');

        // The name reaches SQL as an identifier, so nothing but this pattern may pass.
        if (preg_match('/^pranata_e2e_[a-z0-9_]+\z/', $name) !== 1) {
            throw new RuntimeException("[{$name}] is not an E2E database.");
        }

        DatabaseResetGuard::assertAllowed($this->laravel->environment(), $name);

        match ($action) {
            'create' => Schema::createDatabase($name),
            'drop' => Schema::dropDatabaseIfExists($name),
            default => throw new RuntimeException("Unknown action [{$action}]."),
        };

        $this->info("{$action}: {$name}");

        return self::SUCCESS;
    }
}
