<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;

test('the default language is Indonesian', function () {
    $this->get(route('login'))
        ->assertSee('<html lang="id">', false)
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'id'));
});

test('choosing a language keeps the page and changes the language of the next response', function () {
    $this->from(route('login'))
        ->post(route('locale.update'), ['locale' => 'en'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('locale', 'en');

    $this->get(route('login'))
        ->assertSee('<html lang="en">', false)
        ->assertInertia(fn (Assert $page) => $page->component('auth/login')->where('locale', 'en'));
});

test('an unsupported language is refused and changes nothing', function () {
    $this->from(route('login'))
        ->post(route('locale.update'), ['locale' => 'fr'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('locale')
        ->assertSessionMissing('locale');

    $this->withSession(['locale' => 'fr'])
        ->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->where('locale', 'id'));
});

test('server messages exist in both languages', function (string $key, string $id, string $en) {
    expect(__($key, ['attribute' => 'nama'], 'id'))->toBe($id)
        ->and(__($key, ['attribute' => 'name'], 'en'))->toBe($en);
})->with([
    ['validation.required', 'Kolom nama wajib diisi.', 'The name field is required.'],
    ['auth.failed', 'Data masuk tidak cocok dengan catatan kami.', 'These credentials do not match our records.'],
]);

test('a guest sees no workspace and a signed-in user sees only the home workspace', function () {
    $this->get(route('login'))
        ->assertInertia(fn (Assert $page) => $page->where('workspaces', []));

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('home')
            ->where('workspaces', [['key' => 'home', 'href' => '/home']]));
});

describe('error pages', function () {
    beforeEach(function () {
        config(['app.debug' => false]);

        foreach ([403, 419, 429, 500, 503] as $status) {
            Route::middleware('web')->get("/test-error/{$status}", fn () => abort($status));
        }
    });

    test('each error page renders in the chosen language', function (int $status, string $locale) {
        $this->withSession(['locale' => $locale])
            ->get("/test-error/{$status}")
            ->assertStatus($status)
            ->assertSee("<html lang=\"{$locale}\">", false)
            ->assertInertia(fn (Assert $page) => $page
                ->component('errors/show')
                ->where('status', $status)
                ->where('locale', $locale));
    })->with([403, 419, 429, 500, 503])->with(['id', 'en']);

    test('an unknown address renders the 404 page in the chosen language', function (string $locale) {
        $this->withSession(['locale' => $locale])
            ->get('/alamat-yang-tidak-ada')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('errors/show')
                ->where('status', 404)
                ->where('locale', $locale));
    })->with(['id', 'en']);

    test('recovery from a failed GET stays on the same address', function () {
        $this->get('/test-error/429')
            ->assertInertia(fn (Assert $page) => $page->where('returnUrl', null));
    });

    test('recovery from a failed mutation leads back to the page it came from', function () {
        Route::middleware('web')->put('/test-error/mutation', fn () => abort(429));

        $this->from(route('login'))
            ->put('/test-error/mutation')
            ->assertStatus(429)
            ->assertInertia(fn (Assert $page) => $page->where('returnUrl', route('login')));
    });

    test('recovery from a failed mutation never leaves the application', function (?string $referer) {
        Route::middleware('web')->put('/test-error/mutation', fn () => abort(429));

        $this->withHeaders(array_filter(['referer' => $referer]))
            ->put('/test-error/mutation')
            ->assertInertia(fn (Assert $page) => $page->where('returnUrl', route('home')));
    })->with([
        'another site' => 'https://example.org/page',
        'a look-alike host' => fn () => rtrim(config('app.url'), '/').'.example.org/page',
        'no referer' => null,
    ]);

    test('a JSON client keeps a JSON error', function () {
        $this->getJson('/test-error/403')
            ->assertForbidden()
            ->assertJsonStructure(['message']);
    });
});
