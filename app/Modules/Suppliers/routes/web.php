<?php

use App\Modules\Suppliers\Http\Controllers\SuppliersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('modules/suppliers')->name('modules.suppliers.')->group(function () {
    Route::get('/', [SuppliersController::class, 'index'])->name('index');
    Route::get('/data', [SuppliersController::class, 'data'])->name('data');
    Route::get('/create', [SuppliersController::class, 'create'])->name('create');
    Route::post('/', [SuppliersController::class, 'store'])->name('store');
    Route::get('/{supplier}', [SuppliersController::class, 'show'])->name('show');
    Route::get('/{supplier}/edit', [SuppliersController::class, 'edit'])->name('edit');
    Route::put('/{supplier}', [SuppliersController::class, 'update'])->name('update');
    Route::delete('/{supplier}', [SuppliersController::class, 'destroy'])->name('destroy');

    // API routes for AJAX calls
    Route::get('/api/active', [SuppliersController::class, 'getActive'])->name('api.active');
    Route::get('/api/search', [SuppliersController::class, 'search'])->name('api.search');
});