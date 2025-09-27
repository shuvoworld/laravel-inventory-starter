<?php

use App\Modules\Suppliers\Http\Controllers\SuppliersController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('modules')->name('modules.suppliers.')->group(function () {
    Route::get('suppliers', [SuppliersController::class, 'index'])->name('index');
    Route::get('suppliers/create', [SuppliersController::class, 'create'])->name('create');
    Route::post('suppliers', [SuppliersController::class, 'store'])->name('store');
    Route::get('suppliers/{supplier}', [SuppliersController::class, 'show'])->name('show');
    Route::get('suppliers/{supplier}/edit', [SuppliersController::class, 'edit'])->name('edit');
    Route::put('suppliers/{supplier}', [SuppliersController::class, 'update'])->name('update');
    Route::delete('suppliers/{supplier}', [SuppliersController::class, 'destroy'])->name('destroy');

    // API routes for AJAX calls
    Route::get('api/suppliers/active', [SuppliersController::class, 'getActive'])->name('api.active');
    Route::get('api/suppliers/search', [SuppliersController::class, 'search'])->name('api.search');
});