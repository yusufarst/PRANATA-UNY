<?php

use App\Models\User;
use App\Modules\Identity\Notifications\PasswordWasReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());

    // The test mailer sends nothing, so the reset by email is off unless a test says mail goes out.
    config(['auth.email_reset' => true]);
});

// TASK-011 TAC-05: without mail that goes out, there is no reset by email and no link to it.
test('without mail the reset by email does not exist and the sign-in page offers no link', function () {
    config(['auth.email_reset' => false]);
    Notification::fake();

    $user = User::factory()->create();

    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->component('auth/login')->where('canResetPassword', false));

    $this->get(route('password.request'))->assertNotFound();
    $this->post(route('password.email'), ['email' => $user->email])->assertNotFound();
    $this->get(route('password.reset', 'any-token'))->assertNotFound();
    $this->post(route('password.update'), [
        'token' => 'any-token',
        'email' => $user->email,
        'password' => 'Sandi-Baru-2026',
        'password_confirmation' => 'Sandi-Baru-2026',
    ])->assertNotFound();

    Notification::assertNothingSent();
});

test('with mail the sign-in page offers the reset link', function () {
    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->component('auth/login')->where('canResetPassword', true));
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get(route('password.reset', $notification->token));

        $response->assertOk();

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

// TASK-010 TAC-05: a reset by email ends every session of the account.
test('a password reset ends every session of the account', function () {
    Notification::fake();

    $user = User::factory()->create(['must_change_password' => true]);
    DB::table('sessions')->insert(['id' => 'old-device', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);

    $this->post(route('password.email'), ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $this->post(route('password.update'), [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'Sandi-Baru-2026',
            'password_confirmation' => 'Sandi-Baru-2026',
        ])->assertSessionHasNoErrors();

        return true;
    });

    expect(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and($user->refresh()->must_change_password)->toBeFalse();

    // SEC-20: the owner is also told in the app.
    Notification::assertSentTo($user, PasswordWasReset::class, fn (PasswordWasReset $notification) => $notification->via === PasswordWasReset::EMAIL);
});

test('password cannot be reset with invalid token', function () {
    $user = User::factory()->create();

    $response = $this->post(route('password.update'), [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'newpassword123',
        'password_confirmation' => 'newpassword123',
    ]);

    $response->assertSessionHasErrors('email');
});
