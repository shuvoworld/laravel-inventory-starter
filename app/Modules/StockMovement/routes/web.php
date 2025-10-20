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

    // Manual stock correction routes
    Route::get('/correction', [StockMovementController::class, 'createCorrection'])
        ->middleware('permission:stock-movement.create')
        ->name('correction.create');

    Route::post('/correction', [StockMovementController::class, 'storeCorrection'])
        ->middleware('permission:stock-movement.create')
        ->name('correction.store');

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

    // Report routes
    Route::get('/report', [StockMovementController::class, 'report'])
        ->middleware('permission:stock-movement.view')
        ->name('report');

    Route::get('/product/{productId}/history', [StockMovementController::class, 'productHistory'])
        ->middleware('permission:stock-movement.view')
        ->name('product.history');

    Route::get('/audit/{id}', [StockMovementController::class, 'auditTrail'])
        ->middleware('permission:stock-movement.view')
        ->name('audit');

    Route::get('/export', [StockMovementController::class, 'export'])
        ->middleware('permission:stock-movement.view')
        ->name('export');

    Route::get('/valuation', [StockMovementController::class, 'valuation'])
        ->middleware('permission:stock-movement.view')
        ->name('valuation');

    Route::get('/trends', [StockMovementController::class, 'trends'])
        ->middleware('permission:stock-movement.view')
        ->name('trends');

    // Stock reconciliation routes
    Route::get('/reconcile', [StockMovementController::class, 'reconcile'])
        ->middleware('permission:stock-movement.reconcile')
        ->name('reconcile');

    Route::post('/reconcile', [StockMovementController::class, 'processReconciliation'])
        ->middleware('permission:stock-movement.reconcile')
        ->name('reconcile.process');

    Route::get('/count-sheet', [StockMovementController::class, 'countSheet'])
        ->middleware('permission:stock-movement.view')
        ->name('count-sheet');

    Route::get('/api/stock-from-movements', [StockMovementController::class, 'getStockFromMovements'])
        ->middleware('permission:stock-movement.view')
        ->name('api.stock-from-movements');
});
