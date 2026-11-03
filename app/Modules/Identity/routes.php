<?php

use App\Modules\Identity\Http\Controllers\ForcedPasswordChangeController;
use App\Modules\Identity\Http\Controllers\SessionController;
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

    // The active sessions of the own account, in PG-007 (TASK-011 TAC-04).
    Route::get('settings/sessions', [SessionController::class, 'index'])->name('identity.sessions.index');
    Route::post('settings/sessions/others/revoke', [SessionController::class, 'revokeOthers'])
        ->middleware('idempotent')
        ->name('identity.sessions.revoke-others');
    Route::post('settings/sessions/{session}/revoke', [SessionController::class, 'revoke'])
        ->middleware('idempotent')
        ->where('session', '[0-9a-f]{64}')
        ->name('identity.sessions.revoke');
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
        Route::post('{user:public_id}/suspend', 'suspend')->middleware('idempotent')->name('suspend');
        Route::post('{user:public_id}/reinstate', 'reinstate')->middleware('idempotent')->name('reinstate');
        Route::post('{user:public_id}/deactivate', 'deactivate')->middleware('idempotent')->name('deactivate');
        Route::post('{user:public_id}/resets', 'requestReset')->middleware('idempotent')->name('resets.store');
        Route::post('{user:public_id}/resets/{reset}/approve', 'approveReset')->middleware('idempotent')->name('resets.approve');
        Route::post('{user:public_id}/resets/{reset}/revoke', 'revokeReset')->middleware('idempotent')->name('resets.revoke');
        Route::post('{user:public_id}/sessions/revoke', 'revokeSessions')->middleware('idempotent')->name('sessions.revoke');
    });
