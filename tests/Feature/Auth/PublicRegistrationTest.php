<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('public registration routes do not exist', function () {
    $this->get('/register')->assertNotFound();

    $this->post('/register', [
        'name' => 'Pengguna Uji',
        'email' => 'uji@example.test',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    expect(Route::has('register'))->toBeFalse()
        ->and(Route::has('register.store'))->toBeFalse()
        ->and(User::query()->count())->toBe(0);
});

test('the login page exists', function () {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('auth/login'));
});

test('no page links to registration', function () {
    expect(resource_path('js/pages/auth/register.tsx'))->not->toBeFile();

    foreach (['auth/login', 'welcome'] as $page) {
        expect(file_get_contents(resource_path("js/pages/{$page}.tsx")))
            ->not->toContain('register');
    }
});

test('passkey sign-in stays off', function () {
    $routes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route) => str_contains($route->uri(), 'passkey'));

    expect($routes)->toBeEmpty();
});
