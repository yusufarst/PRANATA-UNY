<?php

use App\Modules\Platform\Support\DatabaseRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * The privileges of pranata_app (DATA_MODEL section 2): SELECT, INSERT and UPDATE on the tables the owner creates, from now on
 * as well, and DELETE only on the framework tables Laravel empties itself. Append-only tables take UPDATE back when they are
 * created (IntegrityTriggers::appendOnly).
 */
return new class extends Migration
{
    private const DELETABLE = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];

    public function up(): void
    {
        DatabaseRoles::assertOwnerSession(DB::connection());

        $app = DatabaseRoles::APP;
        $owner = DatabaseRoles::OWNER;

        DB::statement("grant select, insert, update on all tables in schema public to {$app}");
        DB::statement("grant usage, select on all sequences in schema public to {$app}");
        DB::statement("alter default privileges for role {$owner} in schema public grant select, insert, update on tables to {$app}");
        DB::statement("alter default privileges for role {$owner} in schema public grant usage, select on sequences to {$app}");
        DB::statement("revoke all on table {$this->migrationsTable()} from {$app}");
        DB::statement('grant delete on table '.implode(', ', self::DELETABLE)." to {$app}");
    }

    public function down(): void
    {
        $app = DatabaseRoles::APP;
        $owner = DatabaseRoles::OWNER;

        DB::statement("alter default privileges for role {$owner} in schema public revoke select, insert, update on tables from {$app}");
        DB::statement("alter default privileges for role {$owner} in schema public revoke usage, select on sequences from {$app}");
        DB::statement("revoke all on all tables in schema public from {$app}");
        DB::statement("revoke all on all sequences in schema public from {$app}");
    }

    private function migrationsTable(): string
    {
        $table = config('database.migrations.table', 'migrations');

        return is_string($table) ? $table : 'migrations';
    }
};
