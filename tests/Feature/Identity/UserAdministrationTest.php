<?php

use App\Models\User;
use App\Modules\Identity\Models\PasswordReset;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/*
 * PG-502 Pengguna (TASK-011): the list, detail and form of accounts, the activation code and the deactivation (OD-38).
 */

function administrator(): User
{
    return User::factory()->inRoleGroup(User::SUPER_ADMIN)->create(['name' => 'Admin Utama']);
}

/**
 * @return array<string, string>
 */
function commandKey(): array
{
    return ['X-Idempotency-Key' => (string) Str::uuid()];
}

/**
 * @return array<string, string>
 */
function newAccount(array $overrides = []): array
{
    return [
        'role_group' => User::INTERNAL,
        'username' => 'budi.santoso',
        'name' => 'Budi Santoso',
        'email' => 'budi@example.test',
        'staff_identifier' => '198001012005011001',
        'locale' => 'id',
        ...$overrides,
    ];
}

/**
 * Create the account through PG-502 as a new administrator and give the activation code shown in the answer, then sign out.
 */
function activationCodeOfNewAccount(TestCase $test): string
{
    $response = $test->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.store'), newAccount())
        ->assertOk();

    $test->post(route('logout'));

    return $response->viewData('page')['props']['issuedSecret']['value'];
}

// Authorization: only admin.users.manage (ACCESS_CONTROL section 4.1).
test('a guest is sent to the sign-in page', function () {
    $this->get(route('identity.admin.users.index'))->assertRedirect(route('login'));
});

test('an account without admin.users.manage cannot open or change accounts', function () {
    $other = User::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->get(route('identity.admin.users.index'))->assertForbidden();
    $this->get(route('identity.admin.users.show', $other->public_id))->assertForbidden();
    $this->withHeaders(commandKey())->post(route('identity.admin.users.store'), newAccount())->assertForbidden();
    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.deactivate', $other->public_id), ['reason' => 'Pindah tugas', 'lock_version' => 0])
        ->assertForbidden();

    expect($other->refresh()->status)->toBe(User::ACTIVE)
        ->and(User::query()->where('username', 'budi.santoso')->exists())->toBeFalse();
});

test('the menu offers Pengguna only to an account that may manage users', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.can.manageUsers', false));

    $this->actingAs(administrator())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->where('auth.can.manageUsers', true));
});

test('the list filters by text, status and role group and keeps the filters', function () {
    $admin = administrator();
    User::factory()->create(['name' => 'Siti Aminah', 'username' => 'siti']);
    User::factory()->inRoleGroup(User::VENDOR)->create(['name' => 'CV Maju Jaya', 'username' => 'majujaya']);
    User::factory()->withStatus(User::DEACTIVATED)->create(['name' => 'Siti Lama', 'username' => 'siti.lama']);

    $this->actingAs($admin)
        ->get(route('identity.admin.users.index', ['q' => 'SITI', 'status' => User::ACTIVE]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/users/index')
            ->where('filters', ['q' => 'SITI', 'status' => User::ACTIVE, 'role_group' => ''])
            ->has('users.data', 1)
            ->where('users.data.0.username', 'siti'));

    $this->get(route('identity.admin.users.index', ['role_group' => User::VENDOR, 'status' => 'BOGUS']))
        ->assertInertia(fn (Assert $page) => $page->where('filters.status', '')
            ->has('users.data', 1)
            ->where('users.data.0.username', 'majujaya'));
});

// TASK-011 TAC-01: the administrator never knows the password.
test('creating an account shows its activation code once and stores only the hash', function () {
    $admin = administrator();

    $response = $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.store'), newAccount())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/users/show')
            ->where('account.username', 'budi.santoso')
            ->where('account.role_group', User::INTERNAL)
            ->where('issuedSecret.kind', 'activation')
            ->has('issuedSecret.value'));

    $code = $response->viewData('page')['props']['issuedSecret']['value'];

    $user = User::query()->where('username', 'budi.santoso')->firstOrFail();
    $secret = PasswordReset::query()->where('user_id', $user->id)->sole();

    expect($code)->toMatch('/^[A-Z2-9]{4}-[A-Z2-9]{4}-[A-Z2-9]{4}$/')
        ->and(Hash::check($code, $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->created_by)->toBe($admin->id)
        ->and($secret->channel)->toBe(PasswordReset::ACTIVATION)
        ->and($secret->status)->toBe(PasswordReset::ISSUED)
        ->and($secret->temp_secret_hash)->toBe($user->password)
        ->and($secret->expires_at->diffInHours(now()->addHours(24)))->toBeLessThan(1)
        ->and(DB::table('audit_events')->where('action', 'identity.account_created')->count())->toBe(1)
        ->and(DB::table('audit_events')->whereRaw('coalesce(metadata::text, \'\') like ?', ["%{$code}%"])->exists())->toBeFalse();

    // The detail never shows the code again.
    $this->get(route('identity.admin.users.show', $user->public_id))
        ->assertInertia(fn (Assert $page) => $page->missing('issuedSecret'));
});

test('a resend of the create command creates nothing more and carries no code', function () {
    $headers = commandKey();

    $this->actingAs(administrator())->withHeaders($headers)->post(route('identity.admin.users.store'), newAccount())->assertOk();

    $this->withHeaders($headers)
        ->post(route('identity.admin.users.store'), newAccount())
        ->assertStatus(303)
        ->assertHeader('X-Idempotent-Replay', 'true');

    expect(User::query()->where('username', 'budi.santoso')->count())->toBe(1);
});

test('an account is created only as INTERNAL or VENDOR, with a username and email unused in any case', function () {
    User::factory()->create(['username' => 'Budi.Santoso', 'email' => 'lama@example.test']);

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.store'), newAccount([
            'role_group' => User::SUPER_ADMIN,
            'email' => 'LAMA@example.test',
        ]))
        ->assertSessionHasErrors(['role_group', 'username', 'email']);

    expect(User::query()->count())->toBe(2);
});

test('the new account signs in with its code and must set a password, which uses up the code', function () {
    $code = activationCodeOfNewAccount($this);
    $user = User::query()->where('username', 'budi.santoso')->firstOrFail();

    $this->post(route('login.store'), ['login' => 'budi.santoso', 'password' => $code]);
    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertRedirect(route('identity.password.edit'));

    $this->put(route('identity.password.update'), [
        'current_password' => $code,
        'password' => 'Sandi-Baru-2026',
        'password_confirmation' => 'Sandi-Baru-2026',
    ])->assertSessionHasNoErrors();

    expect(PasswordReset::query()->where('user_id', $user->id)->value('status'))->toBe(PasswordReset::USED)
        ->and($user->refresh()->must_change_password)->toBeFalse();
});

test('an activation code past its life no longer signs in', function () {
    $code = activationCodeOfNewAccount($this);
    $user = User::query()->where('username', 'budi.santoso')->firstOrFail();

    $this->travel(25)->hours();
    $this->post(route('login.store'), ['login' => 'budi.santoso', 'password' => $code])->assertSessionHasErrors('login');

    $this->assertGuest();
    expect(PasswordReset::query()->where('user_id', $user->id)->value('status'))->toBe(PasswordReset::EXPIRED);
});

// Changing the details at the version the administrator saw.
test('an administrator changes the details and the change is audited', function () {
    $user = User::factory()->create(['name' => 'Nama Lama']);

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->put(route('identity.admin.users.update', $user->public_id), [
            'username' => $user->username,
            'name' => 'Nama Baru',
            'email' => $user->email,
            'staff_identifier' => null,
            'locale' => 'en',
            'lock_version' => $user->lock_version,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $user->public_id));

    $user->refresh();

    expect($user->name)->toBe('Nama Baru')
        ->and($user->locale)->toBe('en')
        ->and($user->role_group)->toBe(User::INTERNAL)
        ->and(DB::table('audit_events')->where('action', 'identity.account_updated')->value('changes'))->toContain('Nama Baru');
});

test('a change made at an old version is refused with 409', function () {
    $user = User::factory()->create(['name' => 'Nama Lama']);
    // Another administrator saved first.
    $user->forceFill(['name' => 'Diubah Orang Lain', 'lock_version' => 1])->save();

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->put(route('identity.admin.users.update', $user->public_id), [
            'username' => $user->username,
            'name' => 'Nama Baru',
            'locale' => 'id',
            'lock_version' => 0,
        ])
        ->assertStatus(409)
        ->assertInertia(fn (Assert $page) => $page->component('admin/users/form')
            ->where('account.name', 'Diubah Orang Lain')
            ->where('account.lock_version', 1)
            ->has('errors.lock_version'));

    expect($user->refresh()->name)->toBe('Diubah Orang Lain');
});

// TASK-011 TAC-06, OD-38, RB-13.
test('deactivating ends every session, changes the remember token and keeps the row and its history', function () {
    $admin = administrator();
    $user = User::factory()->create();
    $oldToken = $user->remember_token;
    DB::table('sessions')->insert([
        ['id' => 'laptop', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()],
        ['id' => 'phone', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()],
    ]);

    $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.deactivate', $user->public_id), [
            'reason' => 'Pegawai pindah tugas',
            'lock_version' => $user->lock_version,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $user->public_id));

    $user->refresh();
    $audit = DB::table('audit_events')->where('action', 'identity.account_deactivated')->sole();

    expect($user->status)->toBe(User::DEACTIVATED)
        ->and($user->deactivated_at)->not->toBeNull()
        ->and($user->remember_token)->not->toBe($oldToken)
        ->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(User::query()->whereKey($user->id)->exists())->toBeTrue()
        ->and($audit->reason)->toBe('Pegawai pindah tugas')
        ->and($audit->metadata)->toContain('"ended_sessions": 2');

    $this->post(route('logout'));
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->assertGuest();
});

test('an open session of the account is refused on its next request after the deactivation', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $user->forceFill(['status' => User::DEACTIVATED, 'deactivated_at' => now()])->save();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a remember cookie issued before the deactivation no longer signs in', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password', 'remember' => 'on']);
    $recaller = $user->id.'|'.$user->refresh()->remember_token.'|'.$user->password;

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.deactivate', $user->public_id), ['reason' => 'Kontrak selesai', 'lock_version' => $user->lock_version])
        ->assertSessionHasNoErrors();

    $this->post(route('logout'));
    $this->flushSession();
    $this->withCookie(auth()->guard('web')->getRecallerName(), $recaller)->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('an administrator cannot deactivate their own account or one already deactivated', function () {
    $admin = administrator();
    $gone = User::factory()->withStatus(User::DEACTIVATED)->create();

    $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.deactivate', $admin->public_id), ['reason' => 'Coba sendiri', 'lock_version' => $admin->lock_version])
        ->assertSessionHasErrors('_domain');

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.deactivate', $gone->public_id), ['reason' => 'Sekali lagi', 'lock_version' => $gone->lock_version])
        ->assertSessionHasErrors('_domain');

    expect($admin->refresh()->status)->toBe(User::ACTIVE);
});

test('a deactivated account cannot be edited', function () {
    $gone = User::factory()->withStatus(User::DEACTIVATED)->create();

    $this->actingAs(administrator())
        ->get(route('identity.admin.users.edit', $gone->public_id))
        ->assertRedirect(route('identity.admin.users.show', $gone->public_id));

    $this->get(route('identity.admin.users.show', $gone->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('can', ['update' => false, 'deactivate' => false]));
});

test('the database refuses a DEACTIVATED account without its moment and the reverse', function (string $status, bool $withMoment) {
    $user = User::factory()->create();

    expect(fn () => DB::table('users')->where('id', $user->id)->update([
        'status' => $status,
        'deactivated_at' => $withMoment ? now() : null,
    ]))->toThrow(QueryException::class);
})->with([
    'deactivated without a moment' => [User::DEACTIVATED, false],
    'active with a moment' => [User::ACTIVE, true],
]);

test('the database refuses a reset record an account requested for itself', function () {
    $user = User::factory()->create();

    expect(fn () => PasswordReset::query()->create([
        'user_id' => $user->id,
        'channel' => PasswordReset::ADMIN_ASSISTED,
        'requested_by' => $user->id,
        'temp_secret_hash' => 'x',
        'status' => PasswordReset::ISSUED,
        'expires_at' => now()->addDay(),
    ]))->toThrow(QueryException::class);
});
