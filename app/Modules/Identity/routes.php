<?php

use App\Modules\Identity\Http\Controllers\ForcedPasswordChangeController;
use App\Modules\Identity\Http\Controllers\UserAdministrationController;
use App\Modules\Identity\Providers\IdentityServiceProvider;
use Illuminate\Support\Facades\Route;

// Routes of the Identity module, named identity.*.
Route::middleware('auth')->group(function () {
    // PG-002 (INFORMATION_ARCHITECTURE).
    Route::get('password/change', [ForcedPasswordChangeController::class, 'edit'])->name('identity.password.edit');

    Route::put('password/change', [ForcedPasswordChangeController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('identity.password.update');
});

// PG-502 Pengguna (INFORMATION_ARCHITECTURE section 3.6).
Route::middleware(['auth', 'can:'.IdentityServiceProvider::MANAGE_USERS])
    ->prefix('admin/users')
    ->name('identity.admin.users.')
    ->controller(UserAdministrationController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->middleware('idempotent')->name('store');
        Route::get('{user:public_id}', 'show')->name('show');
        Route::get('{user:public_id}/edit', 'edit')->name('edit');
        Route::put('{user:public_id}', 'update')->middleware('idempotent')->name('update');
        Route::post('{user:public_id}/deactivate', 'deactivate')->middleware('idempotent')->name('deactivate');
    });
