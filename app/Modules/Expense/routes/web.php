<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Expense\Http\Controllers\ExpenseController;

Route::middleware(['auth'])->prefix('modules/expenses')->name('modules.expenses.')->group(function () {
    Route::get('/', [ExpenseController::class, 'index'])
        ->middleware('permission:expense.view')
        ->name('index');

    Route::get('/create', [ExpenseController::class, 'create'])
        ->middleware('permission:expense.create')
        ->name('create');

    Route::post('/', [ExpenseController::class, 'store'])
        ->middleware('permission:expense.create')
        ->name('store');

    Route::get('/data', [ExpenseController::class, 'data'])
        ->middleware('permission:expense.view')
        ->name('data');

    Route::get('/{id}/edit', [ExpenseController::class, 'edit'])
        ->middleware('permission:expense.edit')
        ->name('edit');

    Route::put('/{id}', [ExpenseController::class, 'update'])
        ->middleware('permission:expense.edit')
        ->name('update');

    Route::delete('/{id}', [ExpenseController::class, 'destroy'])
        ->middleware('permission:expense.delete')
        ->name('destroy');

    Route::get('/{id}', [ExpenseController::class, 'show'])
        ->middleware('permission:expense.view')
        ->name('show');
});