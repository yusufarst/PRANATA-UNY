<?php

use App\Modules\Organization\Http\Controllers\ChangeRequestController;
use App\Modules\Organization\Http\Controllers\OrganizationController;
use App\Modules\Organization\Providers\OrganizationServiceProvider;
use Illuminate\Support\Facades\Route;

// PG-505 Organisasi (INFORMATION_ARCHITECTURE section 3.6), RT-032. Administration needs confirmed TOTP where it is required.
Route::middleware(['auth', 'can:'.OrganizationServiceProvider::MANAGE_ORGANIZATION, 'two-factor'])
    ->prefix('admin/organization')
    ->name('organization.admin.')
    ->group(function () {
        Route::get('/', [OrganizationController::class, 'index'])->name('index');

        Route::controller(ChangeRequestController::class)
            ->prefix('requests')
            ->name('requests.')
            ->group(function () {
                Route::get('create', 'create')->name('create');
                Route::post('/', 'store')->middleware('idempotent')->name('store');
                Route::get('{changeRequest:public_id}', 'show')->name('show');
                Route::get('{changeRequest:public_id}/edit', 'edit')->name('edit');
                Route::put('{changeRequest:public_id}', 'update')->middleware('idempotent')->name('update');
                Route::post('{changeRequest:public_id}/submit', 'submit')->middleware('idempotent')->name('submit');
                Route::post('{changeRequest:public_id}/return', 'returnToDraft')->middleware('idempotent')->name('return');
                Route::post('{changeRequest:public_id}/escalate', 'escalate')->middleware('idempotent')->name('escalate');
                Route::post('{changeRequest:public_id}/decide', 'decide')->middleware('idempotent')->name('decide');
                Route::post('{changeRequest:public_id}/apply', 'apply')->middleware('idempotent')->name('apply');
                Route::post('{changeRequest:public_id}/withdraw', 'withdraw')->middleware('idempotent')->name('withdraw');
            });
    });
