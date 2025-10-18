<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ProductAttribute\Http\Controllers\ProductAttributeController;

Route::middleware(['auth'])->prefix('modules/product-attribute')->name('modules.product-attribute.')->group(function () {
    Route::get('/', [ProductAttributeController::class, 'index'])
        ->middleware('permission:product-attribute.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [ProductAttributeController::class, 'data'])
        ->middleware('permission:product-attribute.view')
        ->name('data');

    Route::get('/create', [ProductAttributeController::class, 'create'])
        ->middleware('permission:product-attribute.create')
        ->name('create');

    Route::post('/', [ProductAttributeController::class, 'store'])
        ->middleware('permission:product-attribute.create')
        ->name('store');

    Route::get('/{id}', [ProductAttributeController::class, 'show'])
        ->middleware('permission:product-attribute.view')
        ->name('show');

    Route::get('/{id}/edit', [ProductAttributeController::class, 'edit'])
        ->middleware('permission:product-attribute.edit')
        ->name('edit');

    Route::put('/{id}', [ProductAttributeController::class, 'update'])
        ->middleware('permission:product-attribute.edit')
        ->name('update');

    Route::delete('/{id}', [ProductAttributeController::class, 'destroy'])
        ->middleware('permission:product-attribute.delete')
        ->name('destroy');
});
