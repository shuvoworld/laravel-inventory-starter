<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PurchaseReturn\Http\Controllers\PurchaseReturnController;

Route::prefix('admin/modules')->name('modules.')->middleware(['web', 'auth'])->group(function () {
    Route::prefix('purchase-return')->name('purchase-return.')->group(function () {
        Route::get('/', [PurchaseReturnController::class, 'index'])->name('index');
        Route::get('/data', [PurchaseReturnController::class, 'data'])->name('data');
        Route::get('/create', [PurchaseReturnController::class, 'create'])->name('create');
        Route::post('/', [PurchaseReturnController::class, 'store'])->name('store');
        Route::get('/{id}', [PurchaseReturnController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PurchaseReturnController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PurchaseReturnController::class, 'update'])->name('update');
        Route::delete('/{id}', [PurchaseReturnController::class, 'destroy'])->name('destroy');
    });
});