<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * The generic trigger functions of DATA_MODEL sections 1, 2 and 19 rule 6. Tables receive them through IntegrityTriggers in
 * the migration of the Task that owns the table; every refusal has its own SQLSTATE (PN001 to PN004).
 *
 * migrate:fresh drops tables but keeps functions, so every function is created or replaced. The two functions that read a
 * parent run as their owner, so a role holding only INSERT on the child needs no right on the parent for the check.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            create or replace function forbid_update_delete() returns trigger
            language plpgsql as $$
            begin
                raise exception '% on % is forbidden: the table is append-only', tg_op, tg_table_name
                    using errcode = 'PN001', hint = 'Record a reversing or correcting row instead.';
            end
            $$;

            -- Arguments: the scope columns that may not change after insert.
            create or replace function forbid_scope_change() returns trigger
            language plpgsql as $$
            declare
                scope_column text;
            begin
                foreach scope_column in array tg_argv loop
                    if (to_jsonb(new) -> scope_column) is distinct from (to_jsonb(old) -> scope_column) then
                        raise exception 'column %.% is a scope column and cannot change after insert', tg_table_name, scope_column
                            using errcode = 'PN002';
                    end if;
                end loop;

                return new;
            end
            $$;

            -- Arguments: parent table, child foreign key, parent status column, final statuses (comma list),
            -- child columns that may still change while the parent is final (comma list, may be empty).
            create or replace function forbid_change_when_parent_final() returns trigger
            language plpgsql security definer set search_path = public, pg_temp as $$
            declare
                parent_table text := tg_argv[0];
                foreign_key text := tg_argv[1];
                status_column text := tg_argv[2];
                final_statuses text[] := string_to_array(tg_argv[3], ',');
                mutable_columns text[] := coalesce(string_to_array(nullif(tg_argv[4], ''), ','), '{}');
                parent_ids text[] := '{}';
                parent_id text;
                parent_status text;
                changed_columns text[];
            begin
                if tg_op in ('UPDATE', 'DELETE') then
                    parent_ids := parent_ids || (to_jsonb(old) ->> foreign_key);
                end if;

                if tg_op in ('INSERT', 'UPDATE') then
                    parent_ids := parent_ids || (to_jsonb(new) ->> foreign_key);
                end if;

                if tg_op = 'UPDATE' then
                    select coalesce(array_agg(n.key), '{}') into changed_columns
                    from jsonb_each(to_jsonb(new)) as n
                    where n.value is distinct from (to_jsonb(old) -> n.key);
                end if;

                foreach parent_id in array parent_ids loop
                    continue when parent_id is null;

                    -- FOR SHARE waits for a finalization in progress and then reads the committed status.
                    execute format('select %I::text from %I where id = $1::bigint for share', status_column, parent_table)
                        into parent_status using parent_id;

                    if parent_status = any (final_statuses)
                        and not (tg_op = 'UPDATE' and changed_columns <@ mutable_columns) then
                        raise exception '% on % is forbidden: % % is %', tg_op, tg_table_name, parent_table, parent_id, parent_status
                            using errcode = 'PN003';
                    end if;
                end loop;

                if tg_op = 'DELETE' then
                    return old;
                end if;

                return new;
            end
            $$;

            -- Arguments: parent table, the column of this row that holds the parent id, the fingerprint function of the parent.
            -- A deferred constraint trigger: at commit the fingerprint is computed again and must equal the stored one.
            create or replace function verify_capture_manifest() returns trigger
            language plpgsql security definer set search_path = public, pg_temp as $$
            declare
                parent_table text := tg_argv[0];
                key_column text := tg_argv[1];
                manifest_function text := tg_argv[2];
                parent_id text := to_jsonb(new) ->> key_column;
                stored text;
                computed text;
                found_parent boolean;
            begin
                if parent_id is null then
                    return null;
                end if;

                execute format('select true, capture_manifest_sha256::text, %I($1::bigint)::text from %I where id = $1::bigint',
                        manifest_function, parent_table)
                    into found_parent, stored, computed using parent_id;

                if found_parent is null or computed is null or computed is distinct from stored then
                    raise exception 'capture seal of % % does not match its recorded rows', parent_table, parent_id
                        using errcode = 'PN004', hint = 'Captured rows are only added in the transaction that records the parent.';
                end if;

                return null;
            end
            $$;
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            drop function if exists verify_capture_manifest();
            drop function if exists forbid_change_when_parent_final();
            drop function if exists forbid_scope_change();
            drop function if exists forbid_update_delete();
            SQL);
    }
};
