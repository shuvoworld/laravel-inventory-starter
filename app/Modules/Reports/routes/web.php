<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Reports\Http\Controllers\ReportsController;

Route::middleware(['auth'])->prefix('modules/reports')->name('modules.reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('index');

    Route::get('/profit-loss', [ReportsController::class, 'profitLoss'])
        ->middleware('permission:reports.view')
        ->name('profit-loss');
});
