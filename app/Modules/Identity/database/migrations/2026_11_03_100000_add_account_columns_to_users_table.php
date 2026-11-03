<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/*
 * The account columns of users (DATA_MODEL section 5): a public id, the role group, a username for sign-in, the account
 * status, the forced password change, the language and the concurrency version. Sign-in matches username and email without
 * regard to case, so both are unique in lower case; email becomes optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
            alter table users
                add column public_id char(26),
                add column role_group varchar(20) not null default 'INTERNAL'
                    check (role_group in ('SUPER_ADMIN', 'INTERNAL', 'VENDOR')),
                add column username varchar(60),
                add column staff_identifier varchar(40),
                add column status varchar(20) not null default 'ACTIVE'
                    check (status in ('ACTIVE', 'SUSPENDED', 'LOCKED', 'DEACTIVATED')),
                add column must_change_password boolean not null default false,
                add column password_changed_at timestamptz,
                add column last_login_at timestamptz,
                add column deactivated_at timestamptz,
                add column locale varchar(5) not null default 'id' check (locale in ('id', 'en')),
                add column created_by bigint references users (id),
                add column lock_version integer not null default 0,
                alter column name type varchar(150),
                alter column email drop not null;
            SQL);

        // A database from before this migration holds no account yet; any row it has gets a usable identity all the same.
        foreach (DB::table('users')->orderBy('id')->pluck('id') as $id) {
            DB::table('users')->where('id', $id)->update([
                'public_id' => strtolower((string) Str::ulid()),
                'username' => "user{$id}",
            ]);
        }

        DB::unprepared(<<<'SQL'
            alter table users
                alter column public_id set not null,
                alter column username set not null,
                alter column role_group drop default,
                add constraint users_public_id_unique unique (public_id),
                add constraint users_username_format check (username ~ '^[A-Za-z0-9._]+$'),
                drop constraint users_email_unique;

            create unique index users_username_lower_unique on users (lower(username));
            create unique index users_email_lower_unique on users (lower(email)) where email is not null;
            SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
            drop index if exists users_email_lower_unique;
            drop index if exists users_username_lower_unique;

            alter table users
                drop constraint users_username_format,
                drop constraint users_public_id_unique,
                drop column public_id,
                drop column role_group,
                drop column username,
                drop column staff_identifier,
                drop column status,
                drop column must_change_password,
                drop column password_changed_at,
                drop column last_login_at,
                drop column deactivated_at,
                drop column locale,
                drop column created_by,
                drop column lock_version,
                alter column name type varchar(255);
            SQL);

        // Rows without an email cannot go back to a required column; a rollback is only for a database that has none.
        DB::statement('alter table users alter column email set not null');
        DB::statement('alter table users add constraint users_email_unique unique (email)');
    }
};
