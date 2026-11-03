<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * The Access tables of DATA_MODEL section 9 (TASK-014). The capabilities themselves live in code (CapabilityCatalog); a profile
 * lists some of them, and a grant gives one profile or one capability to one user within one scope.
 *
 * A privileged grant (requires_approval) is ACTIVE only with an approver who is not its granter (SOD-05, TAC-06): both rules are
 * constraints here as well as checks in the Actions, and so is AUTHZ-20: nobody grants to or approves for themselves. A scope names a unit or a warehouse by scope_id and a policy family by
 * scope_key; GLOBAL and WORKSPACE name neither.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            create table access_profiles (
                id bigint generated always as identity primary key,
                code varchar(60) not null constraint access_profiles_code_uq unique
                    constraint access_profiles_code_chk check (code ~ '^[A-Z][A-Z0-9_]*$'),
                name varchar(150) not null,
                description text,
                workspace varchar(30) constraint access_profiles_workspace_chk
                    check (workspace in ('procurement', 'warehouse', 'asset', 'administration')),
                is_privileged boolean not null default false,
                status varchar(20) not null constraint access_profiles_status_chk check (status in ('ACTIVE', 'INACTIVE')),
                created_at timestamptz not null default now(),
                updated_at timestamptz not null default now()
            );

            create table access_profile_capabilities (
                profile_id bigint not null references access_profiles (id),
                capability varchar(100) not null constraint access_profile_capabilities_capability_chk
                    check (capability ~ '^[a-z][a-z_]*(\.[a-z][a-z_]*)+$'),
                constraint access_profile_capabilities_pk primary key (profile_id, capability)
            );

            create table access_grants (
                id bigint generated always as identity primary key,
                public_id char(26) not null constraint access_grants_public_id_uq unique,
                user_id bigint not null references users (id),
                profile_id bigint references access_profiles (id),
                capability varchar(100) constraint access_grants_capability_chk
                    check (capability ~ '^[a-z][a-z_]*(\.[a-z][a-z_]*)+$'),
                scope_type varchar(20) not null constraint access_grants_scope_type_chk check (scope_type in (
                    'GLOBAL', 'ORG_UNIT', 'ORG_UNIT_SUBTREE', 'WAREHOUSE', 'WORKSPACE', 'POLICY_FAMILY'
                )),
                scope_id bigint,
                scope_key varchar(80),
                requires_approval boolean not null,
                effective_from date not null,
                effective_to date,
                reason text not null,
                granted_by bigint not null references users (id),
                approved_by bigint references users (id),
                approved_at timestamptz,
                status varchar(20) not null constraint access_grants_status_chk
                    check (status in ('PENDING_APPROVAL', 'ACTIVE', 'REVOKED', 'EXPIRED')),
                revoked_by bigint references users (id),
                revoked_at timestamptz,
                revoke_reason text,
                created_at timestamptz not null default now(),
                updated_at timestamptz not null default now(),
                lock_version integer not null default 0,
                constraint access_grants_target_chk check (num_nonnulls(profile_id, capability) = 1),
                constraint access_grants_approver_chk check (approved_by is null or approved_by <> granted_by),
                constraint access_grants_not_self_chk check (user_id <> granted_by),
                constraint access_grants_not_self_approved_chk check (approved_by is null or approved_by <> user_id),
                constraint access_grants_approval_chk check (status <> 'ACTIVE' or not requires_approval or approved_by is not null),
                constraint access_grants_scope_key_chk check ((scope_type = 'POLICY_FAMILY') = (scope_key is not null)),
                constraint access_grants_scope_id_chk check (
                    (scope_type in ('ORG_UNIT', 'ORG_UNIT_SUBTREE', 'WAREHOUSE')) = (scope_id is not null)
                ),
                constraint access_grants_window_chk check (effective_to is null or effective_to > effective_from),
                constraint access_grants_revoked_chk check (
                    status <> 'REVOKED' or (revoked_by is not null and revoked_at is not null)
                )
            );

            create index access_grants_user_id_idx on access_grants (user_id, status);
            create index access_grants_scope_id_idx on access_grants (scope_type, scope_id);
            create index access_grants_scope_key_idx on access_grants (scope_type, scope_key);
            create index access_grants_profile_id_idx on access_grants (profile_id);
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            drop table if exists access_grants;
            drop table if exists access_profile_capabilities;
            drop table if exists access_profiles;
            SQL);
    }
};
