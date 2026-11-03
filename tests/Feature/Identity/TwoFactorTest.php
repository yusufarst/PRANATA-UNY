<?php

use App\Models\User;
use App\Modules\Identity\Models\TwoFactorReset;
use App\Modules\Identity\Notifications\TwoFactorWasReset;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Route as AppRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;

/*
 * TASK-012: TOTP (RFC 6238) with one-time recovery codes (TAC-01), confirmed TOTP before Administration for a privileged
 * account (TAC-02, SEC-03) and the removal of a lost TOTP by two administrators (TAC-03).
 */

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
});

function totpSecret(): string
{
    return app(Google2FA::class)->generateSecretKey();
}

function totpCode(string $secret): string
{
    return app(Google2FA::class)->getCurrentOtp($secret);
}

/**
 * An account with confirmed TOTP under the given secret and recovery codes.
 *
 * @param  list<string>  $recoveryCodes
 */
function totpAccount(string $roleGroup, string $secret, array $recoveryCodes = ['first-recovery-code', 'second-recovery-code']): User
{
    return User::factory()->inRoleGroup($roleGroup)->create([
        'two_factor_secret' => encrypt($secret),
        'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        'two_factor_confirmed_at' => now(),
    ]);
}

function totpAdministrator(): User
{
    return User::factory()->inRoleGroup(User::SUPER_ADMIN)->withTwoFactor()->create();
}

/**
 * @return array<string, string>
 */
function totpCommandKey(): array
{
    return ['X-Idempotency-Key' => (string) Str::uuid()];
}

/**
 * @return array<string, string>
 */
function totpIdentityCheck(): array
{
    return ['identity_check_method' => 'IN_PERSON', 'identity_check_note' => 'Datang dengan kartu pegawai, ponsel hilang'];
}

function auditCount(string $action): int
{
    return DB::table('audit_events')->where('action', $action)->count();
}

// TAC-01: setup with a QR code or the manual key, which counts only once a code from the app confirms it.
test('an account sets up TOTP, kept encrypted, which counts only once a code confirms it', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('two-factor.enable'))
        ->assertSessionHasNoErrors();

    $user->refresh();
    $secret = decrypt($user->two_factor_secret);

    expect($secret)->toMatch('/^[A-Z2-7]{16,}$/')
        ->and(DB::table('users')->where('id', $user->id)->value('two_factor_secret'))->not->toContain($secret)
        ->and($user->hasEnabledTwoFactorAuthentication())->toBeFalse()
        ->and(auditCount('identity.two_factor_setup_started'))->toBe(1);

    $this->getJson(route('two-factor.qr-code'))->assertOk()->assertJsonStructure(['svg', 'url']);
    $this->getJson(route('two-factor.secret-key'))->assertOk()->assertJson(['secretKey' => $secret]);

    $wrong = str_pad((string) (((int) totpCode($secret) + 500000) % 1000000), 6, '0', STR_PAD_LEFT);

    $this->post(route('two-factor.confirm'), ['code' => $wrong])
        ->assertSessionHasErrorsIn('confirmTwoFactorAuthentication', 'code');

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeFalse();

    $this->post(route('two-factor.confirm'), ['code' => totpCode($secret)])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue()
        ->and($user->recoveryCodes())->toHaveCount(8)
        ->and(DB::table('audit_events')->where('action', 'identity.two_factor_confirmed')->sole()->actor_user_id)->toBe($user->id);
});

test('signing in asks for the TOTP code, refuses a wrong one and accepts the right one', function () {
    $secret = totpSecret();
    $user = totpAccount(User::INTERNAL, $secret);
    $wrong = str_pad((string) (((int) totpCode($secret) + 500000) % 1000000), 6, '0', STR_PAD_LEFT);

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));
    $this->assertGuest();

    $this->post(route('two-factor.login.store'), ['code' => $wrong])->assertSessionHasErrors('code');
    $this->assertGuest();

    expect(auditCount('identity.two_factor_failed'))->toBe(1);

    $this->post(route('two-factor.login.store'), ['code' => totpCode($secret)])->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);
});

test('a recovery code signs in once and is then used up', function () {
    $user = totpAccount(User::INTERNAL, totpSecret());

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->post(route('two-factor.login.store'), ['recovery_code' => 'first-recovery-code'])->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);

    $codes = $user->refresh()->recoveryCodes();

    expect($codes)->toHaveCount(2)
        ->and($codes)->not->toContain('first-recovery-code')
        ->and($codes)->toContain('second-recovery-code')
        ->and(auditCount('identity.two_factor_recovery_used'))->toBe(1)
        ->and(DB::table('audit_events')->whereRaw('coalesce(metadata::text, \'\') like ?', ['%first-recovery-code%'])->exists())->toBeFalse();

    $this->post(route('logout'));
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->post(route('two-factor.login.store'), ['recovery_code' => 'first-recovery-code'])->assertSessionHasErrors('recovery_code');
    $this->assertGuest();
});

test('a TOTP code that signed in once is refused the second time', function () {
    $secret = totpSecret();
    $user = totpAccount(User::INTERNAL, $secret);
    $code = totpCode($secret);

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->post(route('two-factor.login.store'), ['code' => $code])->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($user);

    $this->post(route('logout'));
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->post(route('two-factor.login.store'), ['code' => $code])->assertSessionHasErrors('code');
    $this->assertGuest();
});

// SEC-02: wrong codes at the challenge count toward the lock, and the right password in between does not forget them.
test('ten wrong TOTP codes lock the account and end the challenge', function () {
    $secret = totpSecret();
    $user = totpAccount(User::INTERNAL, $secret);
    $wrong = str_pad((string) (((int) totpCode($secret) + 500000) % 1000000), 6, '0', STR_PAD_LEFT);

    foreach (range(1, 10) as $attempt) {
        // A new challenge every five codes, past the five-a-minute limit of the challenge.
        if ($attempt % 5 === 1) {
            $this->travel(61)->seconds();
            $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
                ->assertRedirect(route('two-factor.login'));
        }

        $this->post(route('two-factor.login.store'), ['code' => $wrong]);
    }

    expect(auditCount('identity.account_locked'))->toBe(1);

    // The challenge is over, and the password is refused while the lock lasts.
    $this->post(route('two-factor.login.store'), ['code' => totpCode($secret)]);
    $this->assertGuest();

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
        ->assertSessionHasErrors(['login' => __('auth.failed')]);
    $this->assertGuest();
});

test('a right TOTP code is refused for an account locked or no longer active since its password', function (string $case) {
    $secret = totpSecret();
    $user = totpAccount(User::INTERNAL, $secret);

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));

    if ($case === 'locked') {
        foreach (range(1, 10) as $attempt) {
            $this->withServerVariables(['REMOTE_ADDR' => "10.0.9.{$attempt}"])
                ->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password']);
        }
    } else {
        $user->forceFill(['status' => User::SUSPENDED])->save();
    }

    $this->post(route('two-factor.login.store'), ['code' => totpCode($secret)])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['login' => __('auth.failed')]);
    $this->assertGuest();

    expect(DB::table('audit_events')->where('action', 'identity.login_succeeded')->exists())->toBeFalse();
})->with(['locked', 'suspended']);

// TAC-02, SEC-03: the server holds a privileged account without confirmed TOTP back from Administration.
test('a privileged account without confirmed TOTP is sent to set it up before Administration', function (bool $started) {
    $admin = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create($started ? ['two_factor_secret' => encrypt(totpSecret())] : []);

    $this->actingAs($admin)
        ->get(route('identity.admin.users.index'))
        ->assertStatus(303)
        ->assertRedirect(route('security.edit'));

    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.store'), ['role_group' => User::INTERNAL, 'username' => 'budi', 'name' => 'Budi'])
        ->assertStatus(303)
        ->assertRedirect(route('security.edit'));

    expect(User::query()->where('username', 'budi')->exists())->toBeFalse();

    $this->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('settings/security')
            ->where('twoFactorEnabled', false)
            ->where('twoFactorRequired', true));
})->with(['not started' => false, 'started, not confirmed' => true]);

test('a privileged account with confirmed TOTP opens Administration', function () {
    $this->actingAs(totpAdministrator())
        ->get(route('identity.admin.users.index'))
        ->assertOk();
});

test('with the requirement off a privileged account opens Administration without TOTP', function () {
    config(['auth.two_factor_required' => false]);

    $this->actingAs(User::factory()->inRoleGroup(User::SUPER_ADMIN)->create())
        ->get(route('identity.admin.users.index'))
        ->assertOk();
});

test('TOTP stays optional for an ordinary account', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertInertia(fn (Assert $page) => $page->where('twoFactorRequired', false));
});

test('every Administration route asks for TOTP where it is required', function () {
    $missing = collect(Route::getRoutes()->getRoutes())
        ->filter(fn (AppRoute $route) => str_starts_with((string) $route->getName(), 'identity.admin.'))
        ->reject(fn (AppRoute $route) => in_array('two-factor', $route->gatherMiddleware(), true))
        ->map(fn (AppRoute $route) => $route->getName())
        ->values();

    expect($missing->all())->toBe([]);
});

test('a privileged account cannot turn its TOTP off, an ordinary one can', function (string $roleGroup, bool $kept) {
    $user = totpAccount($roleGroup, totpSecret());

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->delete(route('two-factor.disable'));

    $kept ? $response->assertSessionHasErrors('_domain') : $response->assertSessionHasNoErrors();

    expect($user->refresh()->hasEnabledTwoFactorAuthentication())->toBe($kept)
        ->and(auditCount('identity.two_factor_disabled'))->toBe($kept ? 0 : 1);
})->with([
    'SUPER_ADMIN' => [User::SUPER_ADMIN, true],
    'INTERNAL' => [User::INTERNAL, false],
]);

// TAC-03: removing the TOTP of an account needs a second administrator.
test('removing the TOTP of an account waits for a second administrator, then ends every session', function () {
    $maker = totpAdministrator();
    $checker = totpAdministrator();
    $target = totpAccount(User::SUPER_ADMIN, totpSecret());
    $rememberToken = $target->remember_token;
    DB::table('sessions')->insert(['id' => 'target-phone', 'user_id' => $target->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($maker)
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $target->public_id));

    $reset = TwoFactorReset::query()->where('user_id', $target->id)->sole();

    expect($reset->status)->toBe(TwoFactorReset::PENDING_APPROVAL)
        ->and($target->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue()
        ->and(auditCount('identity.two_factor_reset_requested'))->toBe(1);

    // The maker sees the request but cannot approve it, nor ask again.
    $this->get(route('identity.admin.users.show', $target->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('twoFactor.enabled', true)
            ->where('twoFactor.required', true)
            ->where('twoFactor.pendingReset.id', $reset->id)
            ->where('twoFactor.pendingReset.requested_by_me', true)
            ->where('twoFactor.can.request', false)
            ->where('twoFactor.can.approve', false)
            ->where('twoFactor.can.revoke', true));

    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
        ->assertSessionHasErrors('_domain');

    expect($target->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();

    $this->actingAs($checker)
        ->get(route('identity.admin.users.show', $target->public_id))
        ->assertInertia(fn (Assert $page) => $page->where('twoFactor.pendingReset.requested_by_me', false)
            ->where('twoFactor.can.approve', true));

    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $target->public_id));

    $target->refresh();
    $audit = DB::table('audit_events')->where('action', 'identity.two_factor_reset_approved')->sole();

    expect($reset->refresh()->status)->toBe(TwoFactorReset::APPROVED)
        ->and($reset->approved_by)->toBe($checker->id)
        ->and($target->two_factor_secret)->toBeNull()
        ->and($target->two_factor_recovery_codes)->toBeNull()
        ->and($target->two_factor_confirmed_at)->toBeNull()
        ->and($target->remember_token)->not->toBe($rememberToken)
        ->and(DB::table('sessions')->where('user_id', $target->id)->exists())->toBeFalse()
        ->and($audit->metadata)->toContain('"approved_by": '.$checker->id)
        ->and($audit->metadata)->toContain('"identity_check_method": "IN_PERSON"')
        ->and(DB::table('notifications')->where('notifiable_id', $target->id)->where('type', TwoFactorWasReset::class)->count())->toBe(1);

    // The password alone signs the account in again, and Administration waits until it sets TOTP up again (TAC-02).
    Auth::logout();
    $this->post(route('login.store'), ['login' => $target->username, 'password' => 'password'])
        ->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($target);
    $this->get(route('identity.admin.users.index'))->assertRedirect(route('security.edit'));
});

test('a TOTP reset request can be cancelled, which keeps the TOTP and ends the request', function () {
    $target = totpAccount(User::INTERNAL, totpSecret());

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
        ->assertSessionHasNoErrors();
    $reset = TwoFactorReset::query()->where('user_id', $target->id)->sole();

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.revoke', [$target->public_id, $reset->id]))
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('identity.admin.users.show', $target->public_id));

    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    expect($reset->refresh()->status)->toBe(TwoFactorReset::REVOKED)
        ->and($target->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue()
        ->and(auditCount('identity.two_factor_reset_revoked'))->toBe(1);
});

test('a TOTP reset is not approved once the account turned its TOTP off itself', function () {
    $target = totpAccount(User::INTERNAL, totpSecret());

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
        ->assertSessionHasNoErrors();
    $reset = TwoFactorReset::query()->where('user_id', $target->id)->sole();

    $target->forceFill(['two_factor_secret' => null, 'two_factor_recovery_codes' => null, 'two_factor_confirmed_at' => null])->save();

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    expect($reset->refresh()->status)->toBe(TwoFactorReset::PENDING_APPROVAL)
        ->and(auditCount('identity.two_factor_reset_approved'))->toBe(0);
});

test('the account a TOTP reset is meant for can neither approve nor cancel it', function () {
    $target = totpAccount(User::SUPER_ADMIN, totpSecret());

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
        ->assertSessionHasNoErrors();
    $reset = TwoFactorReset::query()->where('user_id', $target->id)->sole();

    $this->actingAs($target)
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');
    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.revoke', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    expect($reset->refresh()->status)->toBe(TwoFactorReset::PENDING_APPROVAL)
        ->and($target->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('no TOTP reset is asked for the own account, a deactivated one or one without TOTP', function () {
    $admin = totpAdministrator();
    $deactivated = totpAccount(User::INTERNAL, totpSecret());
    $deactivated->forceFill(['status' => User::DEACTIVATED, 'deactivated_at' => now()])->save();
    $withoutTotp = User::factory()->create();

    foreach ([$admin, $deactivated, $withoutTotp] as $target) {
        $this->actingAs($admin)
            ->withHeaders(totpCommandKey())
            ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
            ->assertSessionHasErrors('_domain');
    }

    expect(TwoFactorReset::query()->exists())->toBeFalse();
});

test('a TOTP reset names a known identity check', function () {
    $target = totpAccount(User::INTERNAL, totpSecret());

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), ['identity_check_method' => 'BOGUS'])
        ->assertSessionHasErrors('identity_check_method');

    expect(TwoFactorReset::query()->exists())->toBeFalse();
});

test('an account without admin.users.manage cannot ask for, approve or cancel a TOTP reset', function () {
    $target = totpAccount(User::INTERNAL, totpSecret());
    $reset = TwoFactorReset::query()->create([
        'user_id' => $target->id,
        'requested_by' => totpAdministrator()->id,
        'identity_check_method' => 'IN_PERSON',
        'status' => TwoFactorReset::PENDING_APPROVAL,
        'expires_at' => now()->addDay(),
    ]);

    $this->actingAs(User::factory()->create());

    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
        ->assertForbidden();
    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$target->public_id, $reset->id]))
        ->assertForbidden();
    $this->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.revoke', [$target->public_id, $reset->id]))
        ->assertForbidden();

    expect($reset->refresh()->status)->toBe(TwoFactorReset::PENDING_APPROVAL)
        ->and($target->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('a TOTP reset request is approved only under its own account and while it is open', function () {
    $target = totpAccount(User::INTERNAL, totpSecret());
    $other = totpAccount(User::INTERNAL, totpSecret());

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.store', $target->public_id), totpIdentityCheck())
        ->assertSessionHasNoErrors();
    $reset = TwoFactorReset::query()->where('user_id', $target->id)->sole();

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$other->public_id, $reset->id]))
        ->assertNotFound();

    // The earlier session has idled out by then, so the approver signs in afresh.
    $this->travel((int) config('auth.one_time_secret_hours') + 1)->hours();
    $this->flushSession();

    $this->actingAs(totpAdministrator())
        ->withHeaders(totpCommandKey())
        ->post(route('identity.admin.users.two-factor-resets.approve', [$target->public_id, $reset->id]))
        ->assertSessionHasErrors('_domain');

    expect($reset->refresh()->status)->toBe(TwoFactorReset::PENDING_APPROVAL)
        ->and($target->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue()
        ->and($other->refresh()->hasEnabledTwoFactorAuthentication())->toBeTrue();
});

test('the database refuses a TOTP reset approved by its requester or the account, or asked for itself', function (string $case) {
    $maker = totpAdministrator();
    $target = totpAccount(User::INTERNAL, totpSecret());

    $row = match ($case) {
        'approved by the requester' => ['requested_by' => $maker->id, 'approved_by' => $maker->id, 'status' => TwoFactorReset::APPROVED],
        'approved by the account' => ['requested_by' => $maker->id, 'approved_by' => $target->id, 'status' => TwoFactorReset::APPROVED],
        'asked for itself' => ['requested_by' => $target->id, 'status' => TwoFactorReset::PENDING_APPROVAL],
        'approved without an approver' => ['requested_by' => $maker->id, 'status' => TwoFactorReset::APPROVED],
    };

    expect(fn () => TwoFactorReset::query()->create([
        'user_id' => $target->id,
        'identity_check_method' => 'IN_PERSON',
        'expires_at' => now()->addDay(),
        ...$row,
    ]))->toThrow(QueryException::class);
})->with(['approved by the requester', 'approved by the account', 'asked for itself', 'approved without an approver']);
