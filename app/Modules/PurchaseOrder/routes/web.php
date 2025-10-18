<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PurchaseOrder\Http\Controllers\PurchaseOrderController;

Route::middleware(['auth'])->prefix('modules/purchase-order')->name('modules.purchase-order.')->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])
        ->middleware('permission:purchase-order.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [PurchaseOrderController::class, 'data'])
        ->middleware('permission:purchase-order.view')
        ->name('data');

    Route::get('/create', [PurchaseOrderController::class, 'create'])
        ->middleware('permission:purchase-order.create')
        ->name('create');

    Route::post('/', [PurchaseOrderController::class, 'store'])
        ->middleware('permission:purchase-order.create')
        ->name('store');

    Route::get('/{id}', [PurchaseOrderController::class, 'show'])
        ->middleware('permission:purchase-order.view')
        ->name('show');

    Route::get('/{id}/edit', [PurchaseOrderController::class, 'edit'])
        ->middleware('permission:purchase-order.edit')
        ->name('edit');

    Route::put('/{id}', [PurchaseOrderController::class, 'update'])
        ->middleware('permission:purchase-order.edit')
        ->name('update');

    Route::delete('/{id}', [PurchaseOrderController::class, 'destroy'])
        ->middleware('permission:purchase-order.delete')
        ->name('destroy');

    Route::post('/{id}/add-payment', [PurchaseOrderController::class, 'addPayment'])
        ->middleware('permission:purchase-order.edit')
        ->name('add-payment');
});
