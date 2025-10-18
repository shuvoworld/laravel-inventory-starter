<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PointOfSale\Http\Controllers\PointOfSaleController;

Route::middleware(['auth'])->prefix('pos')->name('pos.')->group(function () {
    Route::get('/', [PointOfSaleController::class, 'index'])
        ->middleware('permission:sales-order.create')
        ->name('index');

    Route::get('/pos2', [PointOfSaleController::class, 'pos2'])
        ->middleware('permission:sales-order.create')
        ->name('pos2');

    Route::get('/search', [PointOfSaleController::class, 'search'])
        ->middleware('permission:sales-order.create')
        ->name('search');

    Route::post('/add-to-cart', [PointOfSaleController::class, 'addToCart'])
        ->middleware('permission:sales-order.create')
        ->name('add-to-cart');

    Route::post('/update-cart', [PointOfSaleController::class, 'updateCart'])
        ->middleware('permission:sales-order.create')
        ->name('update-cart');

    Route::post('/remove-from-cart', [PointOfSaleController::class, 'removeFromCart'])
        ->middleware('permission:sales-order.create')
        ->name('remove-from-cart');

    Route::post('/clear-cart', [PointOfSaleController::class, 'clearCart'])
        ->middleware('permission:sales-order.create')
        ->name('clear-cart');

    Route::post('/apply-discount', [PointOfSaleController::class, 'applyDiscount'])
        ->middleware('permission:sales-order.create')
        ->name('apply-discount');

    Route::post('/complete-payment', [PointOfSaleController::class, 'completePayment'])
        ->middleware('permission:sales-order.create')
        ->name('complete-payment');

    Route::get('/customer-search', [PointOfSaleController::class, 'searchCustomers'])
        ->middleware('permission:sales-order.create')
        ->name('customer-search');

    Route::post('/add-customer', [PointOfSaleController::class, 'addCustomer'])
        ->middleware('permission:sales-order.create')
        ->name('add-customer');

    Route::post('/remove-customer', [PointOfSaleController::class, 'removeCustomer'])
        ->middleware('permission:sales-order.create')
        ->name('remove-customer');

    Route::post('/hold-order', [PointOfSaleController::class, 'holdOrder'])
        ->middleware('permission:sales-order.create')
        ->name('hold-order');

    Route::get('/print-receipt/{id}', [PointOfSaleController::class, 'printReceipt'])
        ->middleware('permission:sales-order.view')
        ->name('print-receipt');

    Route::post('/quick-add-customer', [PointOfSaleController::class, 'quickAddCustomer'])
        ->middleware('permission:sales-order.create')
        ->name('quick-add-customer');
});