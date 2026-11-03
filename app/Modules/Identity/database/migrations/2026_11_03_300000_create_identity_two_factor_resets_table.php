<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * identity_two_factor_resets: an administrator's request to remove the TOTP of an account whose owner lost their device, after
 * checking who asked (SECURITY_MODEL section 3.1, TASK-012 TAC-03). Every request waits in PENDING_APPROVAL for a second
 * administrator, and no administrator approves a request of their own or one for their own account. Rows are never deleted; a
 * request ends as APPROVED, EXPIRED or REVOKED.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            create table identity_two_factor_resets (
                id bigint generated always as identity primary key,
                user_id bigint not null references users (id),
                requested_by bigint not null references users (id),
                approved_by bigint references users (id),
                identity_check_method varchar(60) not null,
                identity_check_note text,
                status varchar(20) not null check (status in ('PENDING_APPROVAL', 'APPROVED', 'EXPIRED', 'REVOKED')),
                expires_at timestamptz not null,
                decided_at timestamptz,
                created_at timestamptz not null default now(),
                constraint identity_two_factor_resets_not_own check (requested_by <> user_id),
                constraint identity_two_factor_resets_second_admin
                    check (approved_by is null or (approved_by <> requested_by and approved_by <> user_id)),
                constraint identity_two_factor_resets_approved
                    check ((status = 'APPROVED') = (approved_by is not null))
            );

            create index identity_two_factor_resets_user_status_idx on identity_two_factor_resets (user_id, status);
            SQL);
    }

    public function down(): void
    {
        DB::statement('drop table if exists identity_two_factor_resets');
    }
};
