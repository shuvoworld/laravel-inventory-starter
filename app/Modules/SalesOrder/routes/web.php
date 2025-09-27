<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SalesOrder\Http\Controllers\SalesOrderController;

Route::middleware(['auth'])->prefix('modules/sales-order')->name('modules.sales-order.')->group(function () {
    Route::get('/', [SalesOrderController::class, 'index'])
        ->middleware('permission:sales-order.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [SalesOrderController::class, 'data'])
        ->middleware('permission:sales-order.view')
        ->name('data');

    Route::get('/create', [SalesOrderController::class, 'create'])
        ->middleware('permission:sales-order.create')
        ->name('create');

    Route::post('/', [SalesOrderController::class, 'store'])
        ->middleware('permission:sales-order.create')
        ->name('store');

    Route::get('/{id}', [SalesOrderController::class, 'show'])
        ->middleware('permission:sales-order.view')
        ->name('show');

    Route::get('/{id}/edit', [SalesOrderController::class, 'edit'])
        ->middleware('permission:sales-order.edit')
        ->name('edit');

    Route::get('/{id}/invoice', [SalesOrderController::class, 'invoice'])
        ->middleware('permission:sales-order.view')
        ->name('invoice');

    Route::get('/{id}/pos-print', [SalesOrderController::class, 'posPrint'])
        ->middleware('permission:sales-order.view')
        ->name('pos-print');

    Route::put('/{id}', [SalesOrderController::class, 'update'])
        ->middleware('permission:sales-order.edit')
        ->name('update');

    Route::post('/{id}/hold', [SalesOrderController::class, 'holdOrder'])
        ->middleware('permission:sales-order.edit')
        ->name('hold');

    Route::post('/{id}/release', [SalesOrderController::class, 'releaseOrder'])
        ->middleware('permission:sales-order.edit')
        ->name('release');

    Route::post('/{id}/update-payment', [SalesOrderController::class, 'updatePayment'])
        ->middleware('permission:sales-order.edit')
        ->name('update-payment');

    Route::delete('/{id}', [SalesOrderController::class, 'destroy'])
        ->middleware('permission:sales-order.delete')
        ->name('destroy');
});
