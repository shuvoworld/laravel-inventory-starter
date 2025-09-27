<?php

use Illuminate\Support\Facades\Route;
use App\Modules\OperatingExpenses\Http\Controllers\OperatingExpensesController;

Route::middleware(['auth'])->prefix('modules/operating-expenses')->name('modules.operating-expenses.')->group(function () {
    Route::get('/', [OperatingExpensesController::class, 'index'])
        ->middleware('permission:operating-expenses.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [OperatingExpensesController::class, 'data'])
        ->middleware('permission:operating-expenses.view')
        ->name('data');

    Route::get('/dashboard', [OperatingExpensesController::class, 'dashboard'])
        ->middleware('permission:operating-expenses.view')
        ->name('dashboard');

    Route::get('/create', [OperatingExpensesController::class, 'create'])
        ->middleware('permission:operating-expenses.create')
        ->name('create');

    Route::post('/', [OperatingExpensesController::class, 'store'])
        ->middleware('permission:operating-expenses.create')
        ->name('store');

    Route::get('/{id}', [OperatingExpensesController::class, 'show'])
        ->middleware('permission:operating-expenses.view')
        ->name('show');

    Route::get('/{id}/edit', [OperatingExpensesController::class, 'edit'])
        ->middleware('permission:operating-expenses.edit')
        ->name('edit');

    Route::put('/{id}', [OperatingExpensesController::class, 'update'])
        ->middleware('permission:operating-expenses.edit')
        ->name('update');

    Route::delete('/{id}', [OperatingExpensesController::class, 'destroy'])
        ->middleware('permission:operating-expenses.delete')
        ->name('destroy');
});