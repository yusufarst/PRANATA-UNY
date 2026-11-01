<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home')->name('home');

Route::post('locale', LocaleController::class)->name('locale.update');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('home', 'home')->name('dashboard');
});

require __DIR__.'/settings.php';

// Inside the web group, so a 404 page has the session and its language. Route::fallback alone answers GET only,
// which would turn any other method on an unknown address into a 405.
Route::any('{fallbackPlaceholder}', fn () => abort(404))->where('fallbackPlaceholder', '.*')->fallback();
