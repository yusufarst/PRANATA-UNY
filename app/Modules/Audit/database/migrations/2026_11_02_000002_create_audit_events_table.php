<?php

use App\Modules\Audit\Support\AuditPartitions;
use App\Modules\Platform\Support\DatabaseRoles;
use App\Modules\Platform\Support\IntegrityTriggers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * audit_events of DATA_MODEL section 12: monthly range partitions on occurred_at, a default partition for the rest, and
 * append-only through the privileges of pranata_app and forbid_update_delete() (ADR-0010, INV-017).
 */
return new class extends Migration
{
    public function up(): void
    {
        $app = DatabaseRoles::APP;

        DB::unprepared(<<<SQL
            create table audit_events (
                id bigint generated always as identity,
                occurred_at timestamptz not null default now(),
                actor_user_id bigint,
                actor_role_group varchar(60),
                action varchar(100) not null,
                module varchar(30) not null,
                subject_type varchar(80),
                subject_id bigint,
                subject_public_id char(26),
                workspace varchar(40),
                reason text,
                changes jsonb,
                metadata jsonb,
                request_id uuid,
                ip inet,
                user_agent varchar(255),
                primary key (id, occurred_at)
            ) partition by range (occurred_at);

            create index audit_events_subject_idx on audit_events (subject_type, subject_id, occurred_at desc);
            create index audit_events_actor_user_id_idx on audit_events (actor_user_id, occurred_at desc);
            create index audit_events_action_idx on audit_events (action, occurred_at desc);

            create table audit_events_default partition of audit_events default;
            revoke all on table audit_events_default from {$app};

            -- Runs as the owner, so the scheduler can add a month while pranata_app holds no DDL right.
            -- migrate:fresh keeps functions, so the function is created or replaced.
            create or replace function audit_ensure_partition(p_month date) returns text
            language plpgsql security definer set search_path = public, pg_temp as \$\$
            declare
                month_start date := make_date(extract(year from p_month)::int, extract(month from p_month)::int, 1);
                lower_bound timestamptz := month_start::timestamp at time zone 'UTC';
                upper_bound timestamptz := (month_start + interval '1 month') at time zone 'UTC';
                partition_name text := 'audit_events_' || to_char(month_start, 'YYYY_MM');
            begin
                if to_regclass(partition_name) is not null then
                    return partition_name;
                end if;

                -- Audit rows are never moved or deleted, so a month already caught by the default partition stays there.
                if exists (select from audit_events_default where occurred_at >= lower_bound and occurred_at < upper_bound) then
                    raise exception 'audit_events_default holds events of %, so % is not created', to_char(month_start, 'YYYY-MM'), partition_name
                        using errcode = 'PN005', hint = 'See OPERATIONS_RUNBOOK RB-05.';
                end if;

                execute format('create table %I partition of audit_events for values from (%L) to (%L)', partition_name, lower_bound, upper_bound);
                execute format('revoke all on table %I from {$app}', partition_name);

                return partition_name;
            end
            \$\$;

            revoke all on function audit_ensure_partition(date) from public;
            grant execute on function audit_ensure_partition(date) to {$app};
            SQL);

        // Partitions are read and written through audit_events only; the parent keeps SELECT and INSERT for the application.
        IntegrityTriggers::appendOnly('audit_events');

        AuditPartitions::ensure(DB::connection());
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            drop function if exists audit_ensure_partition(date);
            drop table if exists audit_events;
            SQL);
    }
};
