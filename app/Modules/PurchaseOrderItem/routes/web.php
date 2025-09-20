<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PurchaseOrderItem\Http\Controllers\PurchaseOrderItemController;

Route::middleware(['auth'])->prefix('modules/purchase-order-item')->name('modules.purchase-order-item.')->group(function () {
    Route::get('/', [PurchaseOrderItemController::class, 'index'])
        ->middleware('permission:purchase-order-item.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [PurchaseOrderItemController::class, 'data'])
        ->middleware('permission:purchase-order-item.view')
        ->name('data');

    Route::get('/create', [PurchaseOrderItemController::class, 'create'])
        ->middleware('permission:purchase-order-item.create')
        ->name('create');

    Route::post('/', [PurchaseOrderItemController::class, 'store'])
        ->middleware('permission:purchase-order-item.create')
        ->name('store');

    Route::get('/{id}', [PurchaseOrderItemController::class, 'show'])
        ->middleware('permission:purchase-order-item.view')
        ->name('show');

    Route::get('/{id}/edit', [PurchaseOrderItemController::class, 'edit'])
        ->middleware('permission:purchase-order-item.edit')
        ->name('edit');

    Route::put('/{id}', [PurchaseOrderItemController::class, 'update'])
        ->middleware('permission:purchase-order-item.edit')
        ->name('update');

    Route::delete('/{id}', [PurchaseOrderItemController::class, 'destroy'])
        ->middleware('permission:purchase-order-item.delete')
        ->name('destroy');
});
