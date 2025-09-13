<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Permissions\Http\Controllers\PermissionController;

Route::middleware(['auth'])->prefix('modules/permissions')->name('modules.permissions.')->group(function () {
    Route::get('/', [PermissionController::class, 'index'])
        ->middleware('permission:permissions.view')
        ->name('index');

    // Data endpoint for server-side table
    Route::get('/data', [PermissionController::class, 'data'])
        ->middleware('permission:permissions.view')
        ->name('data');

    Route::get('/create', [PermissionController::class, 'create'])
        ->middleware('permission:permissions.create')
        ->name('create');

    Route::post('/', [PermissionController::class, 'store'])
        ->middleware('permission:permissions.create')
        ->name('store');

    Route::get('/{id}', [PermissionController::class, 'show'])
        ->middleware('permission:permissions.view')
        ->name('show');

    Route::get('/{id}/edit', [PermissionController::class, 'edit'])
        ->middleware('permission:permissions.edit')
        ->name('edit');

    Route::put('/{id}', [PermissionController::class, 'update'])
        ->middleware('permission:permissions.edit')
        ->name('update');

    Route::delete('/{id}', [PermissionController::class, 'destroy'])
        ->middleware('permission:permissions.delete')
        ->name('destroy');
});
