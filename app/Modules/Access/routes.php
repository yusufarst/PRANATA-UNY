<?php

use App\Modules\Access\Http\Controllers\AssignmentController;
use App\Modules\Access\Http\Controllers\GrantController;
use App\Modules\Access\Providers\AccessServiceProvider;
use Illuminate\Support\Facades\Route;

// PG-503 Akses (INFORMATION_ARCHITECTURE section 3.6), RT-002. Administration needs confirmed TOTP where it is required; each
// action checks its own capability again in the controller.
Route::middleware(['auth', 'can:'.AccessServiceProvider::ADMINISTER_ACCESS, 'two-factor'])
    ->prefix('admin/access')
    ->name('access.admin.grants.')
    ->controller(GrantController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('create', 'create')->name('create');
        Route::post('/', 'store')->middleware('idempotent')->name('store');
        Route::get('{grant:public_id}', 'show')->name('show');
        Route::post('{grant:public_id}/approve', 'approve')->middleware('idempotent')->name('approve');
        Route::post('{grant:public_id}/revoke', 'revoke')->middleware('idempotent')->name('revoke');
    });

// PG-504 Penugasan proses (TASK-015), RT-002. The list and the object page are for an access administrator; assigning and
// closing check the right to assign on the object in the controller, so the panel on a module page posts here too.
Route::middleware(['auth', 'two-factor'])
    ->prefix('admin/assignments')
    ->name('access.admin.assignments.')
    ->controller(AssignmentController::class)
    ->group(function () {
        Route::middleware('can:'.AccessServiceProvider::ADMINISTER_ASSIGNMENTS)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('object', 'create')->name('create');
        });
        Route::post('/', 'store')->middleware('idempotent')->name('store');
        Route::post('{assignment:public_id}/close', 'close')->middleware('idempotent')->name('close');
    });
