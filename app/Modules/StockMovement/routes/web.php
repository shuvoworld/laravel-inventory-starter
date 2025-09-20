<?php

use Illuminate\Support\Facades\Route;
use App\Modules\StockMovement\Http\Controllers\StockMovementController;

Route::middleware(['auth'])->prefix('modules/stock-movement')->name('modules.stock-movement.')->group(function () {
    Route::get('/', [StockMovementController::class, 'index'])
        ->middleware('permission:stock-movement.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [StockMovementController::class, 'data'])
        ->middleware('permission:stock-movement.view')
        ->name('data');

    Route::get('/create', [StockMovementController::class, 'create'])
        ->middleware('permission:stock-movement.create')
        ->name('create');

    Route::post('/', [StockMovementController::class, 'store'])
        ->middleware('permission:stock-movement.create')
        ->name('store');

    Route::get('/{id}', [StockMovementController::class, 'show'])
        ->middleware('permission:stock-movement.view')
        ->name('show');

    Route::get('/{id}/edit', [StockMovementController::class, 'edit'])
        ->middleware('permission:stock-movement.edit')
        ->name('edit');

    Route::put('/{id}', [StockMovementController::class, 'update'])
        ->middleware('permission:stock-movement.edit')
        ->name('update');

    Route::delete('/{id}', [StockMovementController::class, 'destroy'])
        ->middleware('permission:stock-movement.delete')
        ->name('destroy');
});
