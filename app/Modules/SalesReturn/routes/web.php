<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SalesReturn\Http\Controllers\SalesReturnController;

Route::prefix('admin/modules')->name('modules.')->middleware(['web', 'auth'])->group(function () {
    Route::prefix('sales-return')->name('sales-return.')->group(function () {
        Route::get('/', [SalesReturnController::class, 'index'])->name('index');
        Route::get('/data', [SalesReturnController::class, 'data'])->name('data');
        Route::get('/create', [SalesReturnController::class, 'create'])->name('create');
        Route::post('/', [SalesReturnController::class, 'store'])->name('store');
        Route::get('/{id}', [SalesReturnController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [SalesReturnController::class, 'edit'])->name('edit');
        Route::put('/{id}', [SalesReturnController::class, 'update'])->name('update');
        Route::delete('/{id}', [SalesReturnController::class, 'destroy'])->name('destroy');
    });
});