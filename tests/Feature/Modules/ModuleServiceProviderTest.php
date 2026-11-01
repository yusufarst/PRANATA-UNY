<?php

use App\Models\User;
use App\Modules\Platform\Providers\PlatformServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Tests\Fixtures\Modules\Sample\Providers\SampleServiceProvider;
use Tests\Fixtures\Modules\Sample\SampleRecord;

test('the platform module is registered with its migration path', function () {
    expect(app()->getProvider(PlatformServiceProvider::class))->not->toBeNull()
        ->and(app('migrator')->paths())->toContain(app_path('Modules/Platform/database/migrations'));
});

test('a module provider loads the routes of its module behind their own middleware', function () {
    app()->register(SampleServiceProvider::class);

    $this->get('/sample-module/ping')->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get('/sample-module/ping')
        ->assertNoContent();

    $route = collect(Route::getRoutes()->getRoutes())
        ->first(fn ($route) => $route->uri() === 'sample-module/ping');

    expect($route->gatherMiddleware())->toContain('web')->toContain('auth');
});

test('a module provider loads the migrations of its module', function () {
    app()->register(SampleServiceProvider::class);

    $paths = array_map(realpath(...), app('migrator')->paths());

    expect($paths)->toContain(base_path('tests/Fixtures/Modules/Sample/database/migrations'));
});

test('a module provider registers the policies of its module', function () {
    app()->register(SampleServiceProvider::class);

    $owner = User::factory()->create();
    $other = User::factory()->create();
    $record = new SampleRecord($owner->id);

    expect(Gate::forUser($owner)->allows('view', $record))->toBeTrue()
        ->and(Gate::forUser($other)->allows('view', $record))->toBeFalse();
});
