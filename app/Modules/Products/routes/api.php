<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Products\Http\Controllers\ProductVariantController;
use App\Modules\Products\Http\Controllers\ProductVariantOptionController;

// Variant Options API Routes
Route::prefix('variant-options')->group(function () {
    Route::get('/', [ProductVariantOptionController::class, 'apiIndex']);
    Route::post('/', [ProductVariantOptionController::class, 'apiStore']);
    Route::get('/{id}', [ProductVariantOptionController::class, 'apiShow']);
    Route::put('/{id}', [ProductVariantOptionController::class, 'apiUpdate']);
    Route::delete('/{id}', [ProductVariantOptionController::class, 'apiDestroy']);
    Route::get('/{id}/values', [ProductVariantOptionController::class, 'apiGetValues']);
});

// Product Variants API Routes
Route::prefix('products/{product}/variants')->group(function () {
    Route::get('/', [ProductVariantController::class, 'apiIndex']);
    Route::post('/', [ProductVariantController::class, 'apiStore']);
    Route::get('/{variant}', [ProductVariantController::class, 'apiShow']);
    Route::put('/{variant}', [ProductVariantController::class, 'apiUpdate']);
    Route::delete('/{variant}', [ProductVariantController::class, 'apiDestroy']);
    Route::post('/generate-bulk', [ProductVariantController::class, 'apiGenerateBulk']);
});

// Products API with variant support
Route::get('/products/{product}/variants', [ProductVariantController::class, 'apiIndex']);
Route::get('/products/{product}/variants/{variant}', [ProductVariantController::class, 'apiShow']);