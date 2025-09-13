<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Users\Http\Controllers\UserController;

Route::middleware(['auth'])->prefix('modules/users')->name('modules.users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('index');

    // Data endpoint for server-side table
    Route::get('/data', [UserController::class, 'data'])
        ->middleware('permission:users.view')
        ->name('data');

    Route::get('/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('create');

    Route::post('/', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('store');

    Route::get('/{id}', [UserController::class, 'show'])
        ->middleware('permission:users.view')
        ->name('show');

    Route::get('/{id}/edit', [UserController::class, 'edit'])
        ->middleware('permission:users.edit')
        ->name('edit');

    Route::put('/{id}', [UserController::class, 'update'])
        ->middleware('permission:users.edit')
        ->name('update');

    Route::delete('/{id}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('destroy');
});
