<?php

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response->assertOk();
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'login' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

// TASK-010 TAC-01.
test('an active account signs in by username or by email, without regard to case', function (string $identity) {
    $user = User::factory()->create(['username' => 'budi.santoso', 'email' => 'budi@example.com']);

    $this->post(route('login.store'), ['login' => $identity, 'password' => 'password'])
        ->assertSessionHasNoErrors();

    $this->assertAuthenticatedAs($user);
})->with(['budi.santoso', 'Budi.Santoso', 'budi@example.com', 'BUDI@Example.com']);

test('an account without an email signs in by username', function () {
    $user = User::factory()->create(['username' => 'tanpa_email', 'email' => null]);

    $this->post(route('login.store'), ['login' => 'tanpa_email', 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
});

test('users with two factor enabled are redirected to two factor challenge', function () {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withTwoFactor()->create();

    $response = $this->post(route('login'), [
        'login' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('two-factor.login'));
    $response->assertSessionHas('login.id', $user->id);
    $this->assertGuest();
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'login' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors(['login' => __('auth.failed')]);

    $this->assertGuest();
});

test('an unknown identity gets the same message as a wrong password', function () {
    $this->post(route('login.store'), ['login' => 'tidak.ada', 'password' => 'password'])
        ->assertSessionHasErrors(['login' => __('auth.failed')]);

    $this->assertGuest();
});

// TASK-010 TAC-02: the right password does not help, and the message says no more than for a wrong one.
test('an account that is not active cannot sign in', function (string $status) {
    $user = User::factory()->withStatus($status)->create();

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
        ->assertSessionHasErrors(['login' => __('auth.failed')]);

    $this->assertGuest();
})->with([User::SUSPENDED, User::LOCKED, User::DEACTIVATED]);

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));

    $this->assertGuest();
});

// TASK-010 TAC-03: five attempts a minute per identity and address.
test('users are rate limited', function () {
    $user = User::factory()->create();

    RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

    $response = $this->post(route('login.store'), [
        'login' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertTooManyRequests();
});

test('the sign-in limit counts the identity and the address together', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('login');
    }

    $this->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
        ->assertTooManyRequests();

    // Another address is not held back by the first.
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.2'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
});

// TASK-010 TAC-03: ten failures in fifteen minutes lock the account for fifteen minutes, from any address.
test('ten failed sign-ins lock the account for fifteen minutes', function () {
    $user = User::factory()->create();

    foreach (range(1, 10) as $attempt) {
        $this->withServerVariables(['REMOTE_ADDR' => "10.0.1.{$attempt}"])
            ->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['login' => __('auth.failed')]);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.2.1'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
        ->assertSessionHasErrors(['login' => __('auth.failed')]);
    $this->assertGuest();

    $this->travel(14)->minutes();
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.2.2'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'password'])
        ->assertSessionHasErrors('login');
    $this->assertGuest();

    $this->travel(2)->minutes();
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.2.3'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
    expect($user->refresh()->status)->toBe(User::ACTIVE);
});

test('failures older than the window do not lock the account', function () {
    $user = User::factory()->create();

    foreach (range(1, 9) as $attempt) {
        $this->withServerVariables(['REMOTE_ADDR' => "10.0.3.{$attempt}"])
            ->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password']);
    }

    $this->travel(16)->minutes();
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.3.100'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password']);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.3.101'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
});

test('a successful sign-in clears the failures', function () {
    $user = User::factory()->create();

    foreach (range(1, 9) as $attempt) {
        $this->withServerVariables(['REMOTE_ADDR' => "10.0.4.{$attempt}"])
            ->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password']);
    }

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.4.100'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);
    $this->assertAuthenticatedAs($user);
    $this->post(route('logout'));

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.4.101'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'wrong-password']);
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.4.102'])
        ->post(route('login.store'), ['login' => $user->username, 'password' => 'password']);

    $this->assertAuthenticatedAs($user);
});
