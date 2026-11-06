<?php

use App\Modules\Files\Support\FileGuards;
use App\Modules\Platform\Support\DatabaseRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/*
 * file_objects, file_links and file_sealed_owner_types of DATA_MODEL section 12 (TASK-016, ADR-0009, DI-33).
 *
 * A file object is the identity of stored bytes: its path is random and written once, its size and SHA-256 are computed by the
 * server from the bytes it stored, and nothing about that identity changes afterwards. pranata_app may only raise the
 * classification and record a scan result; guard_file_object_change() refuses everything else for every role.
 *
 * A file link ties a file to the row of an owning module by the name of its table. Its file, owner and purpose never change;
 * it is released once, with the database clock, and never linked again. A link to an owner type registered as sealed
 * (file_sealed_owner_types) is frozen entirely and takes part in the capture seal of its owner: verify_file_link_manifest()
 * dispatches to the seal function of the owner type at commit, as verify_capture_manifest() does for a fixed parent.
 */
return new class extends Migration
{
    public function up(): void
    {
        $app = DatabaseRoles::APP;
        $objectGuard = FileGuards::OBJECT_CHANGE;
        $linkGuard = FileGuards::LINK_CHANGE;
        $seal = FileGuards::CAPTURE_MANIFEST;

        DB::unprepared(<<<SQL
            create table file_objects (
                id bigint generated always as identity primary key,
                public_id char(26) not null constraint file_objects_public_id_uq unique,
                disk varchar(30) not null,
                path varchar(500) not null constraint file_objects_path_uq unique,
                original_name varchar(255) not null,
                mime_type varchar(150) not null,
                size_bytes bigint not null constraint file_objects_size_chk check (size_bytes >= 0),
                sha256 char(64) not null constraint file_objects_sha256_chk check (sha256 ~ '^[0-9a-f]{64}$'),
                classification varchar(20) not null constraint file_objects_classification_chk
                    check (classification in ('INTERNAL', 'CONFIDENTIAL', 'RESTRICTED')),
                scan_status varchar(20) not null default 'NOT_SCANNED' constraint file_objects_scan_status_chk
                    check (scan_status in ('NOT_SCANNED', 'CLEAN', 'INFECTED', 'ERROR')),
                uploaded_by bigint references users (id),
                uploaded_at timestamptz not null default now(),
                constraint file_objects_path_shape_chk check (path !~ '(^|/)\\.\\.(/|$)' and path !~ '^/')
            );

            create index file_objects_sha256_idx on file_objects (sha256);

            create table file_links (
                id bigint generated always as identity primary key,
                file_id bigint not null references file_objects (id),
                owner_type varchar(80) not null constraint file_links_owner_type_chk check (owner_type ~ '^[a-z][a-z0-9_]*$'),
                owner_id bigint not null,
                purpose varchar(60) not null constraint file_links_purpose_chk check (purpose ~ '^[A-Z][A-Z0-9_]*$'),
                label varchar(200),
                linked_by bigint references users (id),
                linked_at timestamptz not null default now(),
                unlinked_by bigint references users (id),
                unlinked_at timestamptz,
                constraint file_links_unlinked_chk check ((unlinked_by is null) = (unlinked_at is null))
            );

            create index file_links_owner_idx on file_links (owner_type, owner_id, purpose);
            create index file_links_file_id_idx on file_links (file_id);

            create table file_sealed_owner_types (
                owner_type varchar(80) primary key constraint file_sealed_owner_types_owner_type_chk check (owner_type ~ '^[a-z][a-z0-9_]*$'),
                seal_function varchar(100) not null constraint file_sealed_owner_types_seal_function_chk check (seal_function ~ '^[a-z][a-z0-9_]*$'),
                registered_in varchar(200) not null
            );

            -- The registry is filled by the migration of the sealed owner only; the application reads it.
            revoke insert, update, delete, truncate on table file_sealed_owner_types from {$app};
            create trigger file_sealed_owner_types_append_only before update or delete on file_sealed_owner_types
                for each row execute function forbid_update_delete();

            -- Column privileges of DATA_MODEL section 2: the identity of a file and the anchors of a link never change through the application.
            revoke update, delete, truncate on table file_objects from {$app};
            grant update (classification, scan_status) on table file_objects to {$app};
            revoke update, delete, truncate on table file_links from {$app};
            grant update (label, unlinked_by, unlinked_at) on table file_links to {$app};

            -- The second layer, for every role: the content identity stays, the classification only rises, and a scan result never goes back to NOT_SCANNED.
            create or replace function guard_file_object_change() returns trigger
            language plpgsql as \$\$
            declare
                frozen_column text;
                ranks constant text[] := array['INTERNAL', 'CONFIDENTIAL', 'RESTRICTED'];
            begin
                if tg_op = 'DELETE' then
                    raise exception 'DELETE on file_objects is forbidden: stored bytes and their identity are kept'
                        using errcode = '{$objectGuard}', hint = 'Different content is always a new file object.';
                end if;

                foreach frozen_column in array array['public_id', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'sha256', 'uploaded_by', 'uploaded_at'] loop
                    if (to_jsonb(new) -> frozen_column) is distinct from (to_jsonb(old) -> frozen_column) then
                        raise exception 'column file_objects.% is part of the content identity and cannot change', frozen_column
                            using errcode = '{$objectGuard}', hint = 'Different content is always a new file object.';
                    end if;
                end loop;

                if array_position(ranks, new.classification) < array_position(ranks, old.classification) then
                    raise exception 'classification of file_objects % cannot be lowered from % to %', old.id, old.classification, new.classification
                        using errcode = '{$objectGuard}';
                end if;

                if new.scan_status = 'NOT_SCANNED' and old.scan_status <> 'NOT_SCANNED' then
                    raise exception 'scan_status of file_objects % cannot go back to NOT_SCANNED from %', old.id, old.scan_status
                        using errcode = '{$objectGuard}';
                end if;

                return new;
            end
            \$\$;

            create trigger file_objects_guard before update or delete on file_objects
                for each row execute function guard_file_object_change();

            -- Runs as the owner so that the lock on a sealed owner row needs no right of the caller on that table.
            create or replace function guard_file_link_change() returns trigger
            language plpgsql security definer set search_path = public, pg_temp as \$\$
            declare
                frozen_column text;
                sealed boolean;
                owner_found boolean;
            begin
                if tg_op = 'INSERT' then
                    if not exists (select from pg_tables where schemaname = current_schema() and tablename = new.owner_type) then
                        raise exception 'owner_type % of file_links is not a table of this schema', new.owner_type
                            using errcode = '{$linkGuard}', hint = 'owner_type is the name of the owning table, as the morph map enforces it.';
                    end if;

                    if new.unlinked_by is not null or new.unlinked_at is not null then
                        raise exception 'a file link is always created active'
                            using errcode = '{$linkGuard}';
                    end if;

                    -- The database clock, whatever the client sent.
                    new.linked_at := now();

                    if exists (select from file_sealed_owner_types where owner_type = new.owner_type) then
                        -- FOR KEY SHARE: the owner row must exist and be visible; a row another transaction has not committed is not.
                        execute format('select true from %I where id = $1 for key share', new.owner_type) into owner_found using new.owner_id;

                        if owner_found is null then
                            raise exception 'sealed owner % % does not exist or is not visible yet', new.owner_type, new.owner_id
                                using errcode = '{$linkGuard}', hint = 'A link to a sealed owner is created in the transaction that records the owner.';
                        end if;
                    end if;

                    return new;
                end if;

                sealed := exists (select from file_sealed_owner_types where owner_type = old.owner_type);

                if sealed then
                    raise exception '% on file_links % is forbidden: its owner type % is sealed', tg_op, old.id, old.owner_type
                        using errcode = '{$linkGuard}', hint = 'A link to a sealed owner is never changed, released or deleted.';
                end if;

                if tg_op = 'DELETE' then
                    return old;
                end if;

                foreach frozen_column in array array['file_id', 'owner_type', 'owner_id', 'purpose', 'linked_by', 'linked_at'] loop
                    if (to_jsonb(new) -> frozen_column) is distinct from (to_jsonb(old) -> frozen_column) then
                        raise exception 'column file_links.% cannot change after insert', frozen_column
                            using errcode = '{$linkGuard}', hint = 'Moving a file means a new link and, where the owner allows it, releasing the old one.';
                    end if;
                end loop;

                if old.unlinked_at is not null then
                    raise exception 'file_links % was released at % and does not change again', old.id, old.unlinked_at
                        using errcode = '{$linkGuard}';
                end if;

                if new.unlinked_at is not null then
                    if new.unlinked_by is null then
                        raise exception 'releasing file_links % needs unlinked_by', old.id
                            using errcode = '{$linkGuard}';
                    end if;

                    -- The database clock, whatever the client sent.
                    new.unlinked_at := now();
                end if;

                return new;
            end
            \$\$;

            create trigger file_links_guard before insert or update or delete on file_links
                for each row execute function guard_file_link_change();

            -- The capture seal of a sealed owner, checked at commit through the seal function its registration names
            -- (the dispatch of verify_capture_manifest() for file_links, DATA_MODEL section 12).
            create or replace function verify_file_link_manifest() returns trigger
            language plpgsql security definer set search_path = public, pg_temp as \$\$
            declare
                manifest_function text;
                stored text;
                computed text;
                found_parent boolean;
            begin
                select seal_function into manifest_function from file_sealed_owner_types where owner_type = new.owner_type;

                if manifest_function is null then
                    return null;
                end if;

                execute format('select true, capture_manifest_sha256::text, %I($1::bigint)::text from %I where id = $1::bigint',
                        manifest_function, new.owner_type)
                    into found_parent, stored, computed using new.owner_id;

                if found_parent is null or computed is null or computed is distinct from stored then
                    raise exception 'capture seal of % % does not match its recorded rows', new.owner_type, new.owner_id
                        using errcode = '{$seal}', hint = 'Captured rows are only added in the transaction that records the parent.';
                end if;

                return null;
            end
            \$\$;

            create constraint trigger file_links_capture_seal after insert on file_links
                deferrable initially deferred for each row execute function verify_file_link_manifest();
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            drop table if exists file_links;
            drop table if exists file_sealed_owner_types;
            drop table if exists file_objects;
            drop function if exists verify_file_link_manifest();
            drop function if exists guard_file_link_change();
            drop function if exists guard_file_object_change();
            SQL);
    }
};
