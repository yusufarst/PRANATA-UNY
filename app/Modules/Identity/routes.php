<?php

use App\Modules\Identity\Http\Controllers\ForcedPasswordChangeController;
use Illuminate\Support\Facades\Route;

// Routes of the Identity module, named identity.*.
Route::middleware('auth')->group(function () {
    Route::get('account/password', [ForcedPasswordChangeController::class, 'edit'])->name('identity.password.edit');

    Route::put('account/password', [ForcedPasswordChangeController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('identity.password.update');
});
