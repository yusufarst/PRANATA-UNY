<?php

use App\Modules\Files\Http\Controllers\EvidenceController;
use App\Modules\Files\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;

// RT-031 evidence in its parent context (PG-012) and the authorized download of COMMAND_AND_API_CONTRACTS section 7. Every
// route checks the owning module's permission on the row in the controller; a privileged account needs its TOTP first.
Route::middleware(['auth', 'two-factor'])->group(function () {
    Route::get('files/{file:public_id}', [FileController::class, 'show'])->name('files.download');

    Route::prefix('files/evidence/{type}/{id}')
        ->where(['type' => '[a-z][a-z0-9_]*', 'id' => '[0-9]+'])
        ->name('files.evidence.')
        ->controller(EvidenceController::class)
        ->group(function () {
            Route::get('/', 'show')->name('show');
            Route::post('/', 'store')->middleware('idempotent')->name('store');
            Route::post('links/{link}/release', 'release')->middleware('idempotent')->name('release');
        });
});
