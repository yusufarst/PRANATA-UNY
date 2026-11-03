<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

test('security page is displayed', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/security')
            ->where('canManageTwoFactor', true)
            ->where('twoFactorEnabled', false),
        );
});

test('security page requires password confirmation when enabled', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    $user = User::factory()->create();

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $response = $this->actingAs($user)
        ->get(route('security.edit'));

    $response->assertRedirect(route('password.confirm'));
});

test('security page renders without two factor when feature is disabled', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/security')
            ->where('canManageTwoFactor', false)
            ->missing('twoFactorEnabled')
            ->missing('requiresConfirmation'),
        );
});

test('password can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('security.edit'));

    expect(Hash::check('Sandi-Baru-2026', $user->refresh()->password))->toBeTrue();
});

// SECURITY_MODEL section 3: the password profile holds everywhere, and a password may not repeat the identity.
test('a new password must meet the password profile', function (string $password) {
    $user = User::factory()->create(['username' => 'Budi.Santoso-1']);

    $this->actingAs($user)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
})->with(['short' => 'Ab-1', 'no symbol' => 'SandiBaru2026', 'no capital' => 'sandi-baru-2026', 'no number' => 'Sandi-Baru-Kuat']);

test('the username or email, in any case, is refused as a password', function (string $password) {
    $user = User::factory()->create(['username' => 'Budi.Santoso-1', 'email' => 'Budi.S-1@example.com']);

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => $password,
            'password_confirmation' => $password,
        ])
        ->assertSessionHasErrors(['password' => __('auth.password_is_identity')]);
})->with(['Budi.Santoso-1', 'bUDI.sANTOSO-1', 'budi.s-1@EXAMPLE.com']);

// TASK-010 TAC-05: a password change ends the account's other sessions and keeps the one that made it.
test('changing the password ends the other sessions of the account', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    DB::table('sessions')->insert([
        ['id' => 'other-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()],
        ['id' => 'someone-else', 'user_id' => $other->id, 'payload' => '', 'last_activity' => time()],
    ]);
    $rememberToken = $user->remember_token;

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ])
        ->assertSessionHasNoErrors();

    expect(DB::table('sessions')->where('id', 'other-device')->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'someone-else')->exists())->toBeTrue()
        ->and($user->refresh()->remember_token)->not->toBe($rememberToken)
        ->and($user->password_changed_at)->not->toBeNull();

    $this->assertAuthenticatedAs($user);
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('security.edit'))
        ->put(route('user-password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect(route('security.edit'));
});
