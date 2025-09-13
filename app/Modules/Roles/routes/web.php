<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Roles\Http\Controllers\RoleController;

Route::middleware(['auth'])->prefix('modules/roles')->name('modules.roles.')->group(function () {
    Route::get('/', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('index');

    // Data endpoint for server-side table
    Route::get('/data', [RoleController::class, 'data'])
        ->middleware('permission:roles.view')
        ->name('data');

    Route::get('/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.create')
        ->name('create');

    Route::post('/', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('store');

    Route::get('/{id}', [RoleController::class, 'show'])
        ->middleware('permission:roles.view')
        ->name('show');

    Route::get('/{id}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.edit')
        ->name('edit');

    Route::put('/{id}', [RoleController::class, 'update'])
        ->middleware('permission:roles.edit')
        ->name('update');

    Route::delete('/{id}', [RoleController::class, 'destroy'])
        ->middleware('permission:roles.delete')
        ->name('destroy');
});
