<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * An account is DEACTIVATED exactly when it has the moment of its deactivation (DATA_MODEL section 5, OD-38). The row itself is
 * never deleted: pranata_app holds no DELETE on users (DATA_MODEL section 2).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            alter table users add constraint users_deactivated_at_matches_status
                check ((status = 'DEACTIVATED') = (deactivated_at is not null))
            SQL);
    }

    public function down(): void
    {
        DB::statement('alter table users drop constraint if exists users_deactivated_at_matches_status');
    }
};
