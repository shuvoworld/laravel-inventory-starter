<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ExpenseCategory\Http\Controllers\ExpenseCategoryController;

Route::middleware(['auth'])->prefix('expense-categories')->name('modules.expense-category.')->group(function () {
    Route::get('/', [ExpenseCategoryController::class, 'index'])
        ->middleware('permission:expense-category.view')
        ->name('index');

    Route::get('/create', [ExpenseCategoryController::class, 'create'])
        ->middleware('permission:expense-category.create')
        ->name('create');

    Route::post('/', [ExpenseCategoryController::class, 'store'])
        ->middleware('permission:expense-category.create')
        ->name('store');

    Route::get('/{id}/edit', [ExpenseCategoryController::class, 'edit'])
        ->middleware('permission:expense-category.edit')
        ->name('edit');

    Route::put('/{id}', [ExpenseCategoryController::class, 'update'])
        ->middleware('permission:expense-category.edit')
        ->name('update');

    Route::delete('/{id}', [ExpenseCategoryController::class, 'destroy'])
        ->middleware('permission:expense-category.delete')
        ->name('destroy');

    Route::get('/data', [ExpenseCategoryController::class, 'data'])
        ->middleware('permission:expense-category.view')
        ->name('data');
});