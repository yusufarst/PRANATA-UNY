<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * identity_password_resets of DATA_MODEL section 5: the one-time secrets an administrator issues, for an activation code or an
 * assisted reset (SECURITY_MODEL section 3.1). Only the hash of a secret is stored. The reset of a privileged account waits in
 * PENDING_APPROVAL until a second administrator approves it, and no administrator approves a request of their own. Rows are
 * never deleted; a secret ends as USED, EXPIRED or REVOKED.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            create table identity_password_resets (
                id bigint generated always as identity primary key,
                user_id bigint not null references users (id),
                channel varchar(20) not null check (channel in ('ACTIVATION', 'ADMIN_ASSISTED', 'SELF_SERVICE_EMAIL')),
                requested_by bigint references users (id),
                approved_by bigint references users (id),
                identity_check_method varchar(60),
                identity_check_note text,
                temp_secret_hash varchar(255),
                status varchar(20) not null
                    check (status in ('PENDING_APPROVAL', 'ISSUED', 'USED', 'EXPIRED', 'REVOKED')),
                expires_at timestamptz not null,
                used_at timestamptz,
                created_at timestamptz not null default now(),
                constraint identity_password_resets_not_own check (requested_by is null or requested_by <> user_id),
                constraint identity_password_resets_second_admin
                    check (approved_by is null or (approved_by <> requested_by and approved_by <> user_id)),
                constraint identity_password_resets_issued_secret
                    check (status not in ('ISSUED', 'USED') or temp_secret_hash is not null)
            );

            create index identity_password_resets_user_status_idx on identity_password_resets (user_id, status);
            SQL);
    }

    public function down(): void
    {
        DB::statement('drop table if exists identity_password_resets');
    }
};
