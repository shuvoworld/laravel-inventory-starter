<?php

use App\Modules\Products\Http\Controllers\ProductController;
use App\Modules\Products\Http\Controllers\ProductVariantOptionController;
use App\Modules\Products\Http\Controllers\ProductVariantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('modules/products')
    ->name('modules.products.')
    ->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/data', [ProductController::class, 'data'])->name('data');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/', [ProductController::class, 'store'])->name('store');

        // Variant Options Management (more specific routes first)
        Route::prefix('variant-options')->name('variant-options.')->group(function () {
            Route::get('/', [ProductVariantOptionController::class, 'index'])->name('index');
            Route::get('/create', [ProductVariantOptionController::class, 'create'])->name('create');
            Route::post('/', [ProductVariantOptionController::class, 'store'])->name('store');
            Route::get('/{variantOption}/edit', [ProductVariantOptionController::class, 'edit'])->name('edit');
            Route::put('/{variantOption}', [ProductVariantOptionController::class, 'update'])->name('update');
            Route::delete('/{variantOption}', [ProductVariantOptionController::class, 'destroy'])->name('destroy');
            Route::get('/{variantOption}/values', [ProductVariantOptionController::class, 'getValues'])->name('values');
            Route::get('/all', function () {
                $options = \App\Modules\Products\Models\ProductVariantOption::where('store_id', auth()->user()->currentStoreId())
                    ->with('values')
                    ->orderBy('display_order')
                    ->get();
                return response()->json(['success' => true, 'options' => $options]);
            })->name('all');
        });

        // Product routes (more generic routes last)
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');

        // Product Variants Management
        Route::prefix('{product}/variants')->name('variants.')->group(function () {
            Route::get('/', [ProductVariantController::class, 'index'])->name('index');
            Route::post('/', [ProductVariantController::class, 'store'])->name('store');
            Route::get('/{variant}', [ProductVariantController::class, 'show'])->name('show');
            Route::put('/{variant}', [ProductVariantController::class, 'update'])->name('update');
            Route::delete('/{variant}', [ProductVariantController::class, 'destroy'])->name('destroy');
            Route::post('/generate-bulk', [ProductVariantController::class, 'generateBulk'])->name('generate-bulk');
        });
    });

// Standalone Variant Management Routes
Route::middleware(['auth'])
    ->prefix('modules/variant-options')
    ->name('modules.variant-options.')
    ->group(function () {
        Route::get('/', [ProductVariantOptionController::class, 'index'])->name('index');
        Route::get('/create', [ProductVariantOptionController::class, 'create'])->name('create');
        Route::post('/', [ProductVariantOptionController::class, 'store'])->name('store');
        Route::get('/{variantOption}/edit', [ProductVariantOptionController::class, 'edit'])->name('edit');
        Route::put('/{variantOption}', [ProductVariantOptionController::class, 'update'])->name('update');
        Route::delete('/{variantOption}', [ProductVariantOptionController::class, 'destroy'])->name('destroy');
        Route::get('/{variantOption}/values', [ProductVariantOptionController::class, 'getValues'])->name('values');
    });

Route::middleware(['auth'])
    ->prefix('modules/product-variant')
    ->name('modules.product-variant.')
    ->group(function () {
        Route::get('/', [ProductVariantController::class, 'variantIndex'])->name('index');
        Route::get('/data', [ProductVariantController::class, 'variantData'])->name('data');
        Route::get('/{variant}', [ProductVariantController::class, 'show'])->name('show');
        Route::get('/{variant}/edit', [ProductVariantController::class, 'edit'])->name('edit');
        Route::put('/{variant}', [ProductVariantController::class, 'updateStandalone'])->name('update');
        Route::delete('/{variant}', [ProductVariantController::class, 'destroyStandalone'])->name('destroy');
    });
