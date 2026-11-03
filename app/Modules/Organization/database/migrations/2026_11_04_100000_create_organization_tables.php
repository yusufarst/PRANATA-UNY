<?php

use App\Modules\Platform\Support\IntegrityTriggers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * The Organization tables of DATA_MODEL section 6 (TASK-013), and the change requests of WF-029 F1/F2 that change them.
 *
 * org_units is a generic hierarchy (OD-04): the unit type is a code, never a fixed list of faculties. path is an ltree of the
 * public ids from the root down, computed by the application on save; a move rewrites the whole subtree in one transaction.
 * org_unit_history is append-only, so a change never overwrites an earlier version (INV-017/018).
 *
 * org_change_requests and org_change_request_transitions hold the lifecycle of WF-029 (TR-281–TR-288) until the generic
 * workflow_transitions of TASK-019 exist: a request is S1 DRAFT, S2 VALIDATED, S3 AWAITING_DECISION, S4 APPROVED, S5 EFFECTIVE,
 * S6 SUPERSEDED, or S7 WITHDRAWN/REJECTED. Nothing changes the organization before S5.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            create table org_units (
                id bigint generated always as identity primary key,
                public_id char(26) not null constraint org_units_public_id_uq unique,
                code varchar(40) not null constraint org_units_code_uq unique,
                name varchar(200) not null,
                short_name varchar(80),
                unit_type varchar(40) not null constraint org_units_unit_type_chk check (unit_type ~ '^[A-Z][A-Z0-9_]*$'),
                parent_id bigint references org_units (id),
                path ltree not null constraint org_units_path_uq unique,
                depth smallint not null constraint org_units_depth_chk check (depth = nlevel(path) - 1),
                status varchar(20) not null constraint org_units_status_chk check (status in ('ACTIVE', 'INACTIVE')),
                effective_from date not null,
                effective_to date,
                legacy_codes jsonb,
                created_at timestamptz not null default now(),
                updated_at timestamptz not null default now(),
                created_by bigint references users (id),
                updated_by bigint references users (id),
                lock_version integer not null default 0,
                constraint org_units_not_own_parent_chk check (parent_id is null or parent_id <> id),
                constraint org_units_window_chk check (effective_to is null or effective_to > effective_from)
            );

            create index org_units_path_idx on org_units using gist (path);
            create index org_units_parent_id_idx on org_units (parent_id);
            create index org_units_name_idx on org_units using gin (name gin_trgm_ops);

            create table org_positions (
                id bigint generated always as identity primary key,
                code varchar(60) not null constraint org_positions_code_uq unique
                    constraint org_positions_code_chk check (code ~ '^[A-Z][A-Z0-9_]*$'),
                name varchar(200) not null,
                org_unit_id bigint references org_units (id),
                description text,
                status varchar(20) not null constraint org_positions_status_chk check (status in ('ACTIVE', 'INACTIVE')),
                created_at timestamptz not null default now(),
                updated_at timestamptz not null default now(),
                created_by bigint references users (id),
                updated_by bigint references users (id)
            );

            create index org_positions_org_unit_id_idx on org_positions (org_unit_id);

            create table org_change_requests (
                id bigint generated always as identity primary key,
                public_id char(26) not null constraint org_change_requests_public_id_uq unique,
                family varchar(2) not null constraint org_change_requests_family_chk check (family in ('F1', 'F2')),
                kind varchar(40) not null constraint org_change_requests_kind_chk check (kind in (
                    'CREATE_UNIT', 'UPDATE_UNIT', 'MOVE_UNIT', 'DEACTIVATE_UNIT', 'ACTIVATE_UNIT',
                    'CREATE_POSITION', 'APPOINT', 'END_APPOINTMENT'
                )),
                org_unit_id bigint references org_units (id),
                status varchar(40) not null constraint org_change_requests_status_chk check (status in (
                    'DRAFT', 'VALIDATED', 'AWAITING_DECISION', 'APPROVED', 'EFFECTIVE', 'SUPERSEDED', 'WITHDRAWN', 'REJECTED'
                )),
                payload jsonb not null,
                reason text not null,
                source_ref varchar(150),
                proposed_effective_from date not null,
                validation jsonb,
                effective_from date,
                decision_ref varchar(150),
                is_simulation boolean not null default false,
                created_by bigint not null references users (id),
                submitted_by bigint references users (id),
                submitted_at timestamptz,
                decided_by bigint references users (id),
                decided_at timestamptz,
                applied_by bigint references users (id),
                applied_at timestamptz,
                closed_reason text,
                created_at timestamptz not null default now(),
                updated_at timestamptz not null default now(),
                lock_version integer not null default 0,
                constraint org_change_requests_family_kind_chk check (
                    (family = 'F1') = (kind in ('CREATE_UNIT', 'UPDATE_UNIT', 'MOVE_UNIT', 'DEACTIVATE_UNIT', 'ACTIVATE_UNIT'))
                ),
                constraint org_change_requests_subject_chk check (kind = 'CREATE_UNIT' or org_unit_id is not null),
                constraint org_change_requests_decided_chk check (
                    status not in ('APPROVED', 'EFFECTIVE', 'SUPERSEDED')
                    or (decided_by is not null and decided_at is not null and effective_from is not null)
                ),
                constraint org_change_requests_effective_chk check (
                    status not in ('EFFECTIVE', 'SUPERSEDED') or (applied_by is not null and applied_at is not null)
                ),
                constraint org_change_requests_no_backdate_chk check (
                    effective_from is null or decided_at is null or effective_from >= (decided_at at time zone 'Asia/Jakarta')::date
                )
            );

            create index org_change_requests_org_unit_id_idx on org_change_requests (org_unit_id, status);
            create index org_change_requests_status_idx on org_change_requests (status);

            create table org_change_request_transitions (
                id bigint generated always as identity primary key,
                change_request_id bigint not null references org_change_requests (id),
                transition_code varchar(10) not null constraint org_change_request_transitions_code_chk
                    check (transition_code ~ '^TR-28[1-8]$'),
                from_state varchar(40),
                to_state varchar(40) not null,
                actor_user_id bigint references users (id),
                acted_at timestamptz not null default now(),
                reason text,
                is_simulation boolean not null default false,
                metadata jsonb,
                request_id uuid
            );

            create index org_change_request_transitions_request_idx on org_change_request_transitions (change_request_id, acted_at);

            create table org_unit_history (
                id bigint generated always as identity primary key,
                org_unit_id bigint not null references org_units (id),
                change_type varchar(20) not null constraint org_unit_history_change_type_chk
                    check (change_type in ('CREATE', 'RENAME', 'MOVE', 'ACTIVATE', 'DEACTIVATE', 'CORRECT')),
                before jsonb,
                after jsonb,
                effective_from date not null,
                decision_ref varchar(150),
                change_request_id bigint references org_change_requests (id),
                is_simulation boolean not null default false,
                changed_by bigint references users (id),
                changed_at timestamptz not null default now()
            );

            create index org_unit_history_org_unit_id_idx on org_unit_history (org_unit_id, changed_at);

            create table org_appointments (
                id bigint generated always as identity primary key,
                user_id bigint not null references users (id),
                position_id bigint not null references org_positions (id),
                org_unit_id bigint references org_units (id),
                effective_from date not null,
                effective_to date,
                decree_ref varchar(150),
                status varchar(20) not null constraint org_appointments_status_chk check (status in ('ACTIVE', 'ENDED')),
                change_request_id bigint references org_change_requests (id),
                created_at timestamptz not null default now(),
                updated_at timestamptz not null default now(),
                created_by bigint references users (id),
                updated_by bigint references users (id),
                constraint org_appointments_window_chk check (effective_to is null or effective_to > effective_from),
                constraint org_appointments_ended_chk check (status <> 'ENDED' or effective_to is not null)
            );

            create index org_appointments_user_id_idx on org_appointments (user_id, status);
            create index org_appointments_position_id_idx on org_appointments (position_id, status);
            create index org_appointments_org_unit_id_idx on org_appointments (org_unit_id, status);
            SQL);

        IntegrityTriggers::appendOnly('org_unit_history');
        IntegrityTriggers::appendOnly('org_change_request_transitions');
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            drop table if exists org_appointments;
            drop table if exists org_unit_history;
            drop table if exists org_change_request_transitions;
            drop table if exists org_change_requests;
            drop table if exists org_positions;
            drop table if exists org_units;
            SQL);
    }
};
