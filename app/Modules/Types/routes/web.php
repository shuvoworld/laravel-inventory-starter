<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Types\Http\Controllers\TypeController;

Route::middleware(['auth'])->prefix('modules/types')->name('modules.types.')->group(function () {
    Route::get('/', [TypeController::class, 'index'])
        ->middleware('permission:types.view')
        ->name('index');

    // Data endpoint for server-side table
    Route::get('/data', [TypeController::class, 'data'])
        ->middleware('permission:types.view')
        ->name('data');

    Route::get('/create', [TypeController::class, 'create'])
        ->middleware('permission:types.create')
        ->name('create');

    Route::post('/', [TypeController::class, 'store'])
        ->middleware('permission:types.create')
        ->name('store');

    Route::get('/{id}', [TypeController::class, 'show'])
        ->middleware('permission:types.view')
        ->name('show');

    Route::get('/{id}/edit', [TypeController::class, 'edit'])
        ->middleware('permission:types.edit')
        ->name('edit');

    Route::put('/{id}', [TypeController::class, 'update'])
        ->middleware('permission:types.edit')
        ->name('update');

    Route::delete('/{id}', [TypeController::class, 'destroy'])
        ->middleware('permission:types.delete')
        ->name('destroy');
});
