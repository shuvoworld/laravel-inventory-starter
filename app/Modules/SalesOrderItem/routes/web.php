<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SalesOrderItem\Http\Controllers\SalesOrderItemController;

Route::middleware(['auth'])->prefix('modules/sales-order-item')->name('modules.sales-order-item.')->group(function () {
    Route::get('/', [SalesOrderItemController::class, 'index'])
        ->middleware('permission:sales-order-item.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [SalesOrderItemController::class, 'data'])
        ->middleware('permission:sales-order-item.view')
        ->name('data');

    Route::get('/create', [SalesOrderItemController::class, 'create'])
        ->middleware('permission:sales-order-item.create')
        ->name('create');

    Route::post('/', [SalesOrderItemController::class, 'store'])
        ->middleware('permission:sales-order-item.create')
        ->name('store');

    Route::get('/{id}', [SalesOrderItemController::class, 'show'])
        ->middleware('permission:sales-order-item.view')
        ->name('show');

    Route::get('/{id}/edit', [SalesOrderItemController::class, 'edit'])
        ->middleware('permission:sales-order-item.edit')
        ->name('edit');

    Route::put('/{id}', [SalesOrderItemController::class, 'update'])
        ->middleware('permission:sales-order-item.edit')
        ->name('update');

    Route::delete('/{id}', [SalesOrderItemController::class, 'destroy'])
        ->middleware('permission:sales-order-item.delete')
        ->name('destroy');
});
