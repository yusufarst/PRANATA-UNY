<?php

use App\Models\User;
use App\Modules\Identity\Actions\RevokeSessions;
use App\Modules\Identity\Events\SessionsRevoked;
use App\Modules\Identity\Models\PasswordReset;
use App\Modules\Identity\Notifications\PasswordWasReset;
use App\Modules\Identity\Support\ActiveSessions;
use App\Modules\Platform\Exceptions\DomainRuleViolation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/*
 * PG-502 Pengguna (TASK-011): the list, detail and form of accounts, the activation code, the deactivation (OD-38) and the
 * assisted reset with its second administrator for a privileged account (RB-08).
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

test('suspending ends every session and changes the remember token, and reinstating lets the own password sign in again', function () {
    $admin = administrator();
    $user = User::factory()->create();
    $oldToken = $user->remember_token;
    DB::table('sessions')->insert(['id' => 'laptop', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.suspend', $user->public_id), ['reason' => 'Cuti panjang', 'lock_version' => $user->lock_version])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $user->public_id));

    $user->refresh();

    expect($user->status)->toBe(User::SUSPENDED)
        ->and($user->deactivated_at)->toBeNull()
        ->and($user->remember_token)->not->toBe($oldToken)
        ->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(DB::table('audit_events')->where('action', 'identity.account_suspended')->value('reason'))->toBe('Cuti panjang');

    $this->get(route('identity.admin.users.show', $user->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('can.suspend', false)->where('can.reinstate', true));

    $this->post(route('logout'));
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->assertGuest();

    $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.reinstate', $user->public_id), ['reason' => 'Kembali bertugas', 'lock_version' => $user->lock_version])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->status)->toBe(User::ACTIVE)
        ->and(DB::table('audit_events')->where('action', 'identity.account_reinstated')->count())->toBe(1);

    $this->post(route('logout'));
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->assertAuthenticatedAs($user);
});

test('an administrator cannot suspend their own account, a deactivated one or one at an old version, nor reinstate an active one', function () {
    $admin = administrator();
    $gone = User::factory()->withStatus(User::DEACTIVATED)->create();
    $user = User::factory()->create();
    $user->forceFill(['name' => 'Diubah Orang Lain', 'lock_version' => 1])->save();

    $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.suspend', $admin->public_id), ['reason' => 'Coba sendiri', 'lock_version' => $admin->lock_version])
        ->assertSessionHasErrors('_domain');

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.suspend', $gone->public_id), ['reason' => 'Sudah nonaktif', 'lock_version' => $gone->lock_version])
        ->assertSessionHasErrors('_domain');

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.reinstate', $user->public_id), ['reason' => 'Tidak ditangguhkan', 'lock_version' => 1])
        ->assertSessionHasErrors('_domain');

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.suspend', $user->public_id), ['reason' => 'Versi lama', 'lock_version' => 0])
        ->assertStatus(409);

    expect($admin->refresh()->status)->toBe(User::ACTIVE)
        ->and($gone->refresh()->status)->toBe(User::DEACTIVATED)
        ->and($user->refresh()->status)->toBe(User::ACTIVE);
});

test('another administrator cannot change the email of a privileged account, which its owner still can', function () {
    $admin = administrator();
    $target = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create(['email' => 'pemilik@example.test']);
    $details = fn (User $user, string $email) => [
        'username' => $user->username,
        'name' => $user->name,
        'email' => $email,
        'staff_identifier' => null,
        'locale' => 'id',
        'lock_version' => $user->lock_version,
    ];

    $this->actingAs($admin)
        ->get(route('identity.admin.users.edit', $target->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('emailLocked', true));

    $this->withHeaders(commandKey())
        ->put(route('identity.admin.users.update', $target->public_id), $details($target, 'penyerang@example.test'))
        ->assertSessionHasErrors('_domain');

    expect($target->refresh()->email)->toBe('pemilik@example.test');

    $this->actingAs($target)
        ->get(route('identity.admin.users.edit', $target->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('emailLocked', false));

    $this->withHeaders(commandKey())
        ->put(route('identity.admin.users.update', $target->public_id), $details($target, 'baru@example.test'))
        ->assertSessionHasNoErrors();

    expect($target->refresh()->email)->toBe('baru@example.test');
});

test('a deactivated account cannot be edited', function () {
    $gone = User::factory()->withStatus(User::DEACTIVATED)->create();

    $this->actingAs(administrator())
        ->get(route('identity.admin.users.edit', $gone->public_id))
        ->assertRedirect(route('identity.admin.users.show', $gone->public_id));

    $this->get(route('identity.admin.users.show', $gone->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('can', [
            'update' => false,
            'deactivate' => false,
            'reset' => false,
            'approveReset' => false,
            'revokeReset' => false,
            'revokeSessions' => false,
        ]));
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

/**
 * @return array<string, string>
 */
function identityCheck(array $overrides = []): array
{
    return ['identity_check_method' => 'STAFF_CARD', 'identity_check_note' => 'Kartu pegawai diperiksa di loket', ...$overrides];
}

// TASK-011 TAC-02, SECURITY_MODEL section 3.1, RB-08.
test('an assisted reset shows a temporary password once, ends every session and records the identity check', function () {
    $admin = administrator();
    $user = User::factory()->create();
    $oldToken = $user->remember_token;
    DB::table('sessions')->insert(['id' => 'laptop', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

    $response = $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $user->public_id), identityCheck())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('admin/users/show')
            ->where('issuedSecret.kind', 'temporary_password')
            ->where('account.must_change_password', true)
            ->has('issuedSecret.value'));

    $password = $response->viewData('page')['props']['issuedSecret']['value'];
    $user->refresh();
    $reset = PasswordReset::query()->where('user_id', $user->id)->sole();
    $audit = DB::table('audit_events')->where('action', 'identity.reset_issued')->sole();

    expect($password)->toMatch('/^[A-Z2-9]{4}(-[A-Z2-9]{4}){3}$/')
        ->and(Hash::check($password, $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and($user->remember_token)->not->toBe($oldToken)
        ->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and($reset->channel)->toBe(PasswordReset::ADMIN_ASSISTED)
        ->and($reset->status)->toBe(PasswordReset::ISSUED)
        ->and($reset->requested_by)->toBe($admin->id)
        ->and($reset->temp_secret_hash)->toBe($user->password)
        ->and($reset->expires_at->diffInHours(now()->addHours(24)))->toBeLessThan(1)
        ->and($audit->metadata)->toContain('STAFF_CARD')
        ->and($audit->metadata)->toContain('"ended_sessions": 1')
        ->and(DB::table('audit_events')->whereRaw('coalesce(metadata::text, \'\') like ?', ["%{$password}%"])->exists())->toBeFalse();

    // SEC-20: the owner is told in the app, without the password.
    $notification = DB::table('notifications')->where('notifiable_id', $user->id)->sole();

    expect($notification->type)->toBe(PasswordWasReset::class)
        ->and($notification->notifiable_type)->toBe(User::class)
        ->and(json_decode($notification->data, true))->toBe(['kind' => 'identity.password_reset', 'via' => 'assisted'])
        ->and($notification->data)->not->toContain($password);

    $this->get(route('identity.admin.users.show', $user->public_id))
        ->assertInertia(fn (Assert $page) => $page->missing('issuedSecret'));
});

test('a reset revokes the activation code still open for the account', function () {
    activationCodeOfNewAccount($this);
    $user = User::query()->where('username', 'budi.santoso')->firstOrFail();

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $user->public_id), identityCheck())
        ->assertOk();

    expect(PasswordReset::query()->where('user_id', $user->id)->where('channel', PasswordReset::ACTIVATION)->value('status'))
        ->toBe(PasswordReset::REVOKED);
});

test('the user signs in with the temporary password and must change it, which uses it up', function () {
    $user = User::factory()->create();

    $response = $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $user->public_id), identityCheck())
        ->assertOk();
    $password = $response->viewData('page')['props']['issuedSecret']['value'];
    $this->post(route('logout'));

    $this->post(route('login.store'), ['login' => $user->username, 'password' => $password]);
    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertRedirect(route('identity.password.edit'));

    $this->put(route('identity.password.update'), [
        'current_password' => $password,
        'password' => 'Sandi-Baru-2026',
        'password_confirmation' => 'Sandi-Baru-2026',
    ])->assertSessionHasNoErrors();

    expect(PasswordReset::query()->where('user_id', $user->id)->value('status'))->toBe(PasswordReset::USED)
        ->and($user->refresh()->must_change_password)->toBeFalse();
});

test('a temporary password past 24 hours no longer signs in', function () {
    $user = User::factory()->create();

    $response = $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $user->public_id), identityCheck())
        ->assertOk();
    $password = $response->viewData('page')['props']['issuedSecret']['value'];
    $this->post(route('logout'));

    $this->travel(25)->hours();
    $this->post(route('login.store'), ['login' => $user->username, 'password' => $password])->assertSessionHasErrors('login');

    $this->assertGuest();
    expect(PasswordReset::query()->where('user_id', $user->id)->value('status'))->toBe(PasswordReset::EXPIRED);
});

test('a reset names a known identity check', function (array $input) {
    $user = User::factory()->create();

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $user->public_id), $input)
        ->assertSessionHasErrors('identity_check_method');

    expect(PasswordReset::query()->exists())->toBeFalse();
})->with([
    'missing' => [['identity_check_note' => 'Tanpa cara']],
    'unknown' => [['identity_check_method' => 'PHONE_CALL']],
]);

test('an administrator cannot reset their own account or a deactivated one through the assisted path', function () {
    $admin = administrator();
    $gone = User::factory()->withStatus(User::DEACTIVATED)->create();
    $password = $admin->password;

    $this->actingAs($admin)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $admin->public_id), identityCheck())
        ->assertSessionHasErrors('_domain');

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $gone->public_id), identityCheck())
        ->assertSessionHasErrors('_domain');

    expect($admin->refresh()->password)->toBe($password)
        ->and(PasswordReset::query()->exists())->toBeFalse();

    $this->get(route('identity.admin.users.show', $admin->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('can.reset', false));
});

test('an account without admin.users.manage cannot reset, approve or revoke', function () {
    $other = User::factory()->create();
    $reset = PasswordReset::query()->create([
        'user_id' => $other->id,
        'channel' => PasswordReset::ADMIN_ASSISTED,
        'status' => PasswordReset::PENDING_APPROVAL,
        'identity_check_method' => 'IN_PERSON',
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs(User::factory()->create());

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $other->public_id), identityCheck())
        ->assertForbidden();
    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.approve', [$other->public_id, $reset->id]))
        ->assertForbidden();
    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.revoke', [$other->public_id, $reset->id]))
        ->assertForbidden();

    expect($reset->refresh()->status)->toBe(PasswordReset::PENDING_APPROVAL);
});

// TASK-011 TAC-03: a privileged account needs a second administrator.
test('the reset of a privileged account waits for a second administrator, who gets the temporary password', function () {
    $maker = administrator();
    $checker = administrator();
    $target = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create();
    $password = $target->password;

    $this->actingAs($maker)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $target->public_id), identityCheck(['identity_check_method' => 'IN_PERSON']))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $target->public_id));

    $reset = PasswordReset::query()->where('user_id', $target->id)->sole();

    expect($reset->status)->toBe(PasswordReset::PENDING_APPROVAL)
        ->and($reset->temp_secret_hash)->toBeNull()
        ->and($target->refresh()->password)->toBe($password)
        ->and(DB::table('audit_events')->where('action', 'identity.reset_requested')->count())->toBe(1)
        ->and(DB::table('notifications')->where('notifiable_id', $target->id)->exists())->toBeFalse();

    // The maker sees the request but cannot approve it, nor ask again.
    $this->get(route('identity.admin.users.show', $target->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('account.privileged', true)
            ->where('pendingReset.id', $reset->id)
            ->where('pendingReset.requested_by_me', true)
            ->where('can.reset', false)
            ->where('can.approveReset', false)
            ->where('can.revokeReset', true));

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $target->public_id), identityCheck())
        ->assertSessionHasErrors('_domain');

    $response = $this->actingAs($checker)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.approve', [$target->public_id, $reset->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('issuedSecret.kind', 'temporary_password')
            ->where('pendingReset', null));

    $temporary = $response->viewData('page')['props']['issuedSecret']['value'];
    $reset->refresh();
    $audit = DB::table('audit_events')->where('action', 'identity.reset_issued')->sole();

    expect($reset->status)->toBe(PasswordReset::ISSUED)
        ->and($reset->approved_by)->toBe($checker->id)
        ->and(Hash::check($temporary, $target->refresh()->password))->toBeTrue()
        ->and($target->must_change_password)->toBeTrue()
        ->and($audit->metadata)->toContain('"approved_by": '.$checker->id)
        ->and(DB::table('audit_events')->whereRaw('coalesce(metadata::text, \'\') like ?', ["%{$temporary}%"])->exists())->toBeFalse()
        ->and(DB::table('notifications')->where('notifiable_id', $target->id)->count())->toBe(1);
});

test('a reset request of a privileged account can be cancelled and then no longer approved', function () {
    $maker = administrator();
    $target = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create();
    $password = $target->password;

    $this->actingAs($maker)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $target->public_id), identityCheck())
        ->assertSessionHasNoErrors();
    $reset = PasswordReset::query()->where('user_id', $target->id)->sole();

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.revoke', [$target->public_id, $reset->id]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $target->public_id));

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    expect($reset->refresh()->status)->toBe(PasswordReset::REVOKED)
        ->and($target->refresh()->password)->toBe($password)
        ->and(DB::table('audit_events')->where('action', 'identity.reset_revoked')->count())->toBe(1);
});

test('the account a reset is meant to recover cannot cancel it', function () {
    $target = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create();

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $target->public_id), identityCheck())
        ->assertSessionHasNoErrors();
    $reset = PasswordReset::query()->where('user_id', $target->id)->sole();

    $this->actingAs($target)
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.revoke', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    expect($reset->refresh()->status)->toBe(PasswordReset::PENDING_APPROVAL);
});

test('a reset request is approved only under its own account', function () {
    $target = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create();
    $other = User::factory()->create();

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.store', $target->public_id), identityCheck())
        ->assertSessionHasNoErrors();
    $reset = PasswordReset::query()->where('user_id', $target->id)->sole();

    $this->actingAs(administrator())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.resets.approve', [$other->public_id, $reset->id]))
        ->assertNotFound();

    expect($reset->refresh()->status)->toBe(PasswordReset::PENDING_APPROVAL);
});

test('the database refuses an approval by the administrator who asked for the reset', function () {
    $maker = administrator();
    $target = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create();

    expect(fn () => PasswordReset::query()->create([
        'user_id' => $target->id,
        'channel' => PasswordReset::ADMIN_ASSISTED,
        'requested_by' => $maker->id,
        'approved_by' => $maker->id,
        'temp_secret_hash' => 'x',
        'status' => PasswordReset::ISSUED,
        'expires_at' => now()->addDay(),
    ]))->toThrow(QueryException::class);
});

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

/**
 * Two database sessions of the account, the phone used last.
 */
function twoSessions(User $user): void
{
    DB::table('sessions')->insert([
        ['id' => 'laptop', 'user_id' => $user->id, 'ip_address' => '10.0.0.1', 'user_agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0', 'payload' => '', 'last_activity' => time() - 60],
        ['id' => 'phone', 'user_id' => $user->id, 'ip_address' => '10.0.0.2', 'user_agent' => 'Mozilla/5.0 (Linux; Android 14) Mobile', 'payload' => '', 'last_activity' => time()],
    ]);
}

// TASK-011 TAC-04: the account sees and ends its own sessions.
test('the sessions page lists the own sessions by a key that is not the session id', function () {
    $user = User::factory()->create();
    twoSessions($user);
    DB::table('sessions')->insert(['id' => 'other-account', 'user_id' => User::factory()->create()->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($user)
        ->get(route('identity.sessions.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('settings/sessions')
            ->has('sessions', 2)
            ->where('sessions.0.key', ActiveSessions::key('phone'))
            ->where('sessions.0.ip_address', '10.0.0.2')
            ->where('sessions.0.current', false)
            ->where('sessions.1.key', ActiveSessions::key('laptop'))
            ->missing('sessions.0.id'));
});

test('the current session is marked and is never ended by the own commands', function () {
    $user = User::factory()->create();
    twoSessions($user);
    $this->actingAs($user);

    expect(collect(ActiveSessions::of($user, 'laptop'))->firstWhere('current', true)['key'])->toBe(ActiveSessions::key('laptop'));

    expect(fn () => app(RevokeSessions::class)->handle($user, SessionsRevoked::ONE, ActiveSessions::key('laptop'), 'laptop'))
        ->toThrow(DomainRuleViolation::class);

    expect(app(RevokeSessions::class)->handle($user, SessionsRevoked::OTHERS, keepSessionId: 'laptop'))->toBe(1)
        ->and(DB::table('sessions')->where('user_id', $user->id)->pluck('id')->all())->toBe(['laptop']);
});

test('the account ends one of its sessions, which changes the remember token and is audited', function () {
    $user = User::factory()->create();
    $oldToken = $user->remember_token;
    twoSessions($user);

    $this->actingAs($user)
        ->withHeaders(commandKey())
        ->post(route('identity.sessions.revoke', ActiveSessions::key('laptop')))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.sessions.index'));

    $audit = DB::table('audit_events')->where('action', 'identity.sessions_revoked')->sole();

    expect(DB::table('sessions')->where('user_id', $user->id)->pluck('id')->all())->toBe(['phone'])
        ->and($user->refresh()->remember_token)->not->toBe($oldToken)
        ->and($audit->actor_user_id)->toEqual($user->id)
        ->and($audit->metadata)->toContain('"scope": "one"')
        ->and($audit->metadata)->toContain('"ended_sessions": 1')
        ->and($audit->metadata)->not->toContain('laptop');
});

test('the account ends every other session', function () {
    $user = User::factory()->create();
    twoSessions($user);

    $this->actingAs($user)
        ->withHeaders(commandKey())
        ->post(route('identity.sessions.revoke-others'))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.sessions.index'));

    expect(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and(DB::table('audit_events')->where('action', 'identity.sessions_revoked')->value('metadata'))->toContain('"scope": "others"');
});

test('an account cannot end the session of another account by its key', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    twoSessions($other);

    $this->actingAs($user)
        ->withHeaders(commandKey())
        ->post(route('identity.sessions.revoke', ActiveSessions::key('laptop')))
        ->assertSessionHasErrors('_domain');

    expect(DB::table('sessions')->where('user_id', $other->id)->count())->toBe(2)
        ->and(DB::table('audit_events')->where('action', 'identity.sessions_revoked')->exists())->toBeFalse();
});

// TASK-011 TAC-04: an administrator ends every session of another account.
test('an administrator sees and ends every session of an account', function () {
    $admin = administrator();
    $user = User::factory()->create();
    $oldToken = $user->remember_token;
    twoSessions($user);

    $this->actingAs($admin)
        ->get(route('identity.admin.users.show', $user->public_id))
        ->assertInertia(fn (Assert $page) => $page->has('sessions', 2)->where('can.revokeSessions', true));

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.sessions.revoke', $user->public_id))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $user->public_id));

    $audit = DB::table('audit_events')->where('action', 'identity.sessions_revoked')->sole();

    expect(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and($user->refresh()->remember_token)->not->toBe($oldToken)
        ->and($user->status)->toBe(User::ACTIVE)
        ->and($audit->actor_user_id)->toEqual($admin->id)
        ->and($audit->metadata)->toContain('"scope": "all"')
        ->and($audit->metadata)->toContain('"ended_sessions": 2');
});

test('an administrator ends their own sessions only on their own sessions page', function () {
    $admin = administrator();
    twoSessions($admin);

    $this->actingAs($admin)
        ->get(route('identity.admin.users.show', $admin->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('can.revokeSessions', false));

    $this->withHeaders(commandKey())
        ->post(route('identity.admin.users.sessions.revoke', $admin->public_id))
        ->assertSessionHasErrors('_domain');

    expect(DB::table('sessions')->where('user_id', $admin->id)->count())->toBe(2);
});

test('an account without admin.users.manage cannot end the sessions of another account', function () {
    $other = User::factory()->create();
    twoSessions($other);

    $this->actingAs(User::factory()->create())
        ->withHeaders(commandKey())
        ->post(route('identity.admin.users.sessions.revoke', $other->public_id))
        ->assertForbidden();

    expect(DB::table('sessions')->where('user_id', $other->id)->count())->toBe(2);
});
