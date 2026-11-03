<?php

use App\Models\User;
use App\Modules\Identity\Events\PasswordChanged;
use App\Modules\Identity\Http\Middleware\EnsureSessionIsCurrent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

function auditActions(): array
{
    return DB::table('audit_events')->orderBy('id')->pluck('action')->all();
}

// TASK-010 TAC-04: the forced change holds every other page until it is done.
test('an account that must change its password is held on the change page', function (string $route) {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)->get(route($route))->assertRedirect(route('identity.password.edit'));
})->with(['dashboard', 'profile.edit']);

test('the change page shows for an account that must change its password', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->get(route('identity.password.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/change-password')->has('passwordRules'));
});

test('signing out and the language choice stay open during the forced change', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en'])->assertRedirect();
    $this->post(route('logout'))->assertRedirect(route('home'));

    $this->assertGuest();
});

test('the forced change sets the new password, frees the account and ends the other sessions', function () {
    $user = User::factory()->mustChangePassword()->create();
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

    $this->actingAs($user)
        ->put(route('identity.password.update'), [
            'current_password' => 'password',
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $user->refresh();

    expect(Hash::check('Sandi-Baru-2026', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse()
        ->and(auditActions())->toContain('identity.password_changed');

    $this->get(route('dashboard'))->assertOk();
});

test('the forced change needs the current password and a different new one', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->put(route('identity.password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ])
        ->assertSessionHasErrors('current_password');

    expect($user->refresh()->must_change_password)->toBeTrue();
});

test('an account without a forced change is sent past the change page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('identity.password.edit'))->assertRedirect(route('dashboard'));
});

// SECURITY_MODEL section 4, SEC-05: idle and absolute timeouts.
test('a session ends after the idle minutes without a request', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $this->travel(config('session.idle_minutes') + 1)->minutes();

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status', __('auth.session_ended'));

    $this->assertGuest();
    expect(auditActions())->toContain('identity.session_ended');
});

test('a request within the idle minutes keeps the session going', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->travel(config('session.idle_minutes') - 1)->minutes();
    $this->get(route('dashboard'))->assertOk();
    $this->travel(config('session.idle_minutes') - 1)->minutes();
    $this->get(route('dashboard'))->assertOk();

    $this->assertAuthenticatedAs($user);
});

test('a SUPER_ADMIN session has the shorter idle limit', function () {
    $user = User::factory()->inRoleGroup(User::SUPER_ADMIN)->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->travel(config('session.super_admin_idle_minutes') + 1)->minutes();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
});

test('a session ends after the absolute minutes whatever the activity', function () {
    $user = User::factory()->create();
    $now = now()->getTimestamp();

    $this->actingAs($user)
        ->withSession([
            EnsureSessionIsCurrent::SIGNED_IN_AT => $now - (config('session.absolute_minutes') + 1) * 60,
            EnsureSessionIsCurrent::LAST_ACTIVITY_AT => $now,
        ])
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('an account that is no longer active loses the session on the next request', function (string $status) {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $user->forceFill(['status' => $status, 'deactivated_at' => $status === User::DEACTIVATED ? now() : null])->save();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->assertGuest();
})->with([User::SUSPENDED, User::LOCKED, User::DEACTIVATED]);

test('a timed-out mutation is sent to the sign-in page with 303', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->travel(config('session.idle_minutes') + 1)->minutes();

    $this->put(route('user-password.update'), [])->assertStatus(303)->assertRedirect(route('login'));
});

test('a mutation during the forced change is sent to the change page with 303', function () {
    $user = User::factory()->mustChangePassword()->create();

    $this->actingAs($user)
        ->put(route('user-password.update'), [])
        ->assertStatus(303)
        ->assertRedirect(route('identity.password.edit'));
});

test('the stored session outlives the idle timeout, so the timeout is what ends it', function () {
    expect(config('session.lifetime'))->toBeGreaterThan(config('session.idle_minutes'));
});

// SECURITY_MODEL section 4: no remember cookie for a SUPER_ADMIN.
test('remember me gives the cookie to an ordinary account only', function (string $roleGroup, bool $remembered) {
    $user = User::factory()->inRoleGroup($roleGroup)->create();
    $cookie = Auth::guard('web')->getRecallerName();

    $response = $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password', 'remember' => 'on']);

    $this->assertAuthenticatedAs($user);
    $remembered ? $response->assertCookie($cookie) : $response->assertCookieMissing($cookie);
    expect(DB::table('audit_events')->where('action', 'identity.login_succeeded')->value('metadata'))
        ->toContain($remembered ? 'true' : 'false');
})->with([
    'INTERNAL' => [User::INTERNAL, true],
    'SUPER_ADMIN' => [User::SUPER_ADMIN, false],
]);

test('a SUPER_ADMIN with two-factor sign-in is not remembered after the challenge either', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication(['confirm' => true, 'confirmPassword' => true]);

    $user = User::factory()->inRoleGroup(User::SUPER_ADMIN)->withTwoFactor()->create();

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password', 'remember' => 'on'])
        ->assertRedirect(route('two-factor.login'))
        ->assertSessionHas('login.remember', false);
});

// The password confirmation of Security settings checks the password of the signed-in account.
test('the password confirmation accepts the right password and refuses a wrong one', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('password.confirm.store'), ['password' => 'wrong-password'])
        ->assertSessionHasErrors('password');

    $this->post(route('password.confirm.store'), ['password' => 'password'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('auth.password_confirmed_at');
});

test('a password change that cannot be audited is not made', function () {
    $user = User::factory()->mustChangePassword()->create();
    DB::table('sessions')->insert(['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
    Event::listen(PasswordChanged::class, fn () => throw new RuntimeException('audit write failed'));

    $this->actingAs($user)
        ->put(route('identity.password.update'), [
            'current_password' => 'password',
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ])
        ->assertServerError();

    $user->refresh();

    expect(Hash::check('password', $user->password))->toBeTrue()
        ->and($user->must_change_password)->toBeTrue()
        ->and(DB::table('sessions')->where('id', 'other-device')->exists())->toBeTrue();
});

// An account without an email (DATA_MODEL section 5) has nothing to verify.
test('an account without an email reaches the verified pages after signing in', function () {
    $user = User::factory()->create(['username' => 'tanpa_email', 'email' => null, 'email_verified_at' => null]);

    $this->post(route('login.store'), ['login' => 'tanpa_email', 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
    $this->get(route('dashboard'))->assertOk();
    $this->get(route('profile.edit'))->assertInertia(fn (Assert $page) => $page->where('mustVerifyEmail', false));
});

// TASK-010 TAC-05 and the audit of sign-in events.
test('signing in, failing and signing out are audited without the typed identity', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), ['login' => 'tidak.ada', 'password' => 'password']);
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password']);
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->assertAuthenticatedAs($user);
    $this->post(route('logout'));
    $this->assertGuest();

    expect(auditActions())->toBe(['identity.login_failed', 'identity.login_failed', 'identity.login_succeeded', 'identity.logout'])
        ->and(DB::table('audit_events')->whereRaw('metadata::text like ?', ['%tidak.ada%'])->exists())->toBeFalse()
        ->and($user->refresh()->last_login_at)->not->toBeNull();
});

test('ten failures audit the lock of the account', function () {
    $user = User::factory()->create();

    foreach (range(1, 10) as $attempt) {
        $this->withServerVariables(['REMOTE_ADDR' => "10.0.5.{$attempt}"])
            ->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password']);
    }

    expect(auditActions())->toContain('identity.account_locked');
});

// Saved language (users.locale).
test('a signed-in user keeps the chosen language for the next sign-in', function () {
    $user = User::factory()->create(['locale' => 'id']);

    $this->actingAs($user)->post(route('locale.update'), ['locale' => 'en'])->assertRedirect();

    expect($user->refresh()->locale)->toBe('en');

    $this->post(route('logout'));
    $this->flushSession();

    // The guest's session is Indonesian; signing in brings the saved language back.
    $this->post(route('locale.update'), ['locale' => 'id']);
    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);

    $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
});

test('a language chosen in another session does not switch an open one', function () {
    $user = User::factory()->create(['locale' => 'id']);

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $user->forceFill(['locale' => 'en'])->save();

    $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->where('locale', 'id'));
});
