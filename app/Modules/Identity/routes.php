<?php

use App\Modules\Identity\Http\Controllers\ForcedPasswordChangeController;
use Illuminate\Support\Facades\Route;

// Routes of the Identity module, named identity.*.
Route::middleware('auth')->group(function () {
    // PG-002 (INFORMATION_ARCHITECTURE).
    Route::get('password/change', [ForcedPasswordChangeController::class, 'edit'])->name('identity.password.edit');

    Route::put('password/change', [ForcedPasswordChangeController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('identity.password.update');
});
