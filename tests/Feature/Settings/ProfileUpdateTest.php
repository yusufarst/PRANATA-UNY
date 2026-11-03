<?php

use App\Models\User;
use App\Modules\Platform\Http\Middleware\EnsureIdempotentCommand;
use Illuminate\Support\Str;

// profile.update is a material command, so every request carries its own idempotency key.
beforeEach(function () {
    $this->withHeader(EnsureIdempotentCommand::HEADER, (string) Str::uuid());
});

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('a name longer than the column is refused, not stored', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), ['name' => str_repeat('a', 151), 'email' => $user->email])
        ->assertSessionHasErrors('name');

    expect($user->refresh()->name)->not->toBe(str_repeat('a', 151));
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->email)->toBe('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('a mixed-case email is stored in lowercase and still allows login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'Test.User@Example.com',
        ])
        ->assertSessionHasNoErrors();

    expect($user->refresh()->email)->toBe('test.user@example.com');

    $this->post(route('logout'));
    $this->assertGuest();

    $this->post(route('login.store'), [
        'login' => 'Test.User@Example.com',
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
});

test('a mixed-case email cannot duplicate an existing address', function () {
    User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => 'Taken@Example.com',
        ])
        ->assertSessionHasErrors('email');
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

// OD-38: an account ends only as DEACTIVATED, by an administrator; RouteIntegrityTest proves profile.destroy is gone.
test('a user cannot delete the own account', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete('/settings/profile', ['password' => 'password'])
        ->assertMethodNotAllowed();

    expect($user->fresh())->not->toBeNull();
    $this->assertAuthenticatedAs($user);
});
