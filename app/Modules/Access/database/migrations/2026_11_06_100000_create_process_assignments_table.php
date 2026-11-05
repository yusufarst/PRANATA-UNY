<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * process_assignments of DATA_MODEL section 9 (TASK-015): one user in one assignment role on one object of a module, such as the
 * PPK of a package (ACCESS_CONTROL section 5). The object is named by its type and id, so the table never reaches into the
 * module that owns it. sod_check keeps the result of the separation-of-duties rules when the assignment was made.
 *
 * An assignment ends (ENDED) when its task is over or is revoked (REVOKED) when it was wrong; both keep the row as history with
 * who closed it and why. Nobody assigns themselves, and one person holds a role on an object at most once at a time. The evidence
 * file (evidence_file_id) arrives with the Files module (TASK-016).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            create table process_assignments (
                id bigint generated always as identity primary key,
                public_id char(26) not null constraint process_assignments_public_id_uq unique,
                subject_type varchar(80) not null constraint process_assignments_subject_type_chk
                    check (subject_type ~ '^[a-z][a-z_]*(\.[a-z][a-z_]*)+$'),
                subject_id bigint not null,
                assignment_role varchar(60) not null constraint process_assignments_role_chk
                    check (assignment_role ~ '^[A-Z][A-Z0-9_]*$'),
                user_id bigint not null references users (id),
                effective_from timestamptz not null,
                effective_to timestamptz,
                decree_ref varchar(150),
                sod_check jsonb not null,
                assigned_by bigint not null references users (id),
                status varchar(20) not null constraint process_assignments_status_chk
                    check (status in ('ACTIVE', 'ENDED', 'REVOKED')),
                closed_by bigint references users (id),
                closed_at timestamptz,
                close_reason text,
                created_at timestamptz not null default now(),
                updated_at timestamptz not null default now(),
                lock_version integer not null default 0,
                constraint process_assignments_not_self_chk check (user_id <> assigned_by),
                constraint process_assignments_window_chk check (effective_to is null or effective_to > effective_from),
                constraint process_assignments_sod_check_chk check (jsonb_typeof(sod_check) = 'object'),
                constraint process_assignments_closed_chk check (
                    (status = 'ACTIVE') = (closed_by is null and closed_at is null and close_reason is null)
                )
            );

            create index process_assignments_subject_idx on process_assignments (subject_type, subject_id, assignment_role, status);
            create index process_assignments_user_id_idx on process_assignments (user_id, status);
            create unique index process_assignments_one_active_uq on process_assignments (subject_type, subject_id, assignment_role, user_id)
                where status = 'ACTIVE';
            SQL);
    }

    public function down(): void
    {
        DB::statement('drop table if exists process_assignments');
    }
};
