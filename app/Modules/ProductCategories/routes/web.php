<?php

use App\Modules\ProductCategories\Http\Controllers\ProductCategoriesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('modules/product-categories')
    ->name('modules.product-categories.')
    ->group(function () {
        Route::get('/', [ProductCategoriesController::class, 'index'])
            ->middleware('permission:product-categories.view')
            ->name('index');
        Route::get('/data', [ProductCategoriesController::class, 'data'])
            ->middleware('permission:product-categories.view')
            ->name('data');
        Route::get('/create', [ProductCategoriesController::class, 'create'])
            ->middleware('permission:product-categories.create')
            ->name('create');
        Route::post('/', [ProductCategoriesController::class, 'store'])
            ->middleware('permission:product-categories.create')
            ->name('store');
        Route::get('/{id}/edit', [ProductCategoriesController::class, 'edit'])
            ->middleware('permission:product-categories.edit')
            ->name('edit');
        Route::put('/{id}', [ProductCategoriesController::class, 'update'])
            ->middleware('permission:product-categories.edit')
            ->name('update');
        Route::delete('/{id}', [ProductCategoriesController::class, 'destroy'])
            ->middleware('permission:product-categories.delete')
            ->name('destroy');
    });
