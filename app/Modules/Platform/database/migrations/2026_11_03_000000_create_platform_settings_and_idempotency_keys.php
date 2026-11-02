<?php

use App\Modules\Platform\Support\DatabaseRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * platform_settings and platform_idempotency_keys of DATA_MODEL section 4. Idempotency keys are framework rows that the
 * scheduler deletes once they expire (COMMAND_AND_API_CONTRACTS section 4), so pranata_app may delete them.
 */
return new class extends Migration
{
    public function up(): void
    {
        $app = DatabaseRoles::APP;

        DB::unprepared(<<<SQL
            create table platform_settings (
                key varchar(100) primary key,
                value jsonb not null,
                updated_by bigint references users (id),
                updated_at timestamptz
            );

            create table platform_idempotency_keys (
                id bigint generated always as identity primary key,
                user_id bigint not null references users (id),
                idempotency_key uuid not null,
                route_name varchar(150) not null,
                request_sha256 char(64) not null,
                status varchar(20) not null check (status in ('IN_PROGRESS', 'COMPLETED', 'FAILED')),
                result jsonb,
                created_at timestamptz not null default now(),
                expires_at timestamptz not null,
                unique (user_id, idempotency_key)
            );

            create index platform_idempotency_keys_expires_at_idx on platform_idempotency_keys (expires_at);

            grant delete on table platform_idempotency_keys to {$app};
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            drop table if exists platform_idempotency_keys;
            drop table if exists platform_settings;
            SQL);
    }
};
