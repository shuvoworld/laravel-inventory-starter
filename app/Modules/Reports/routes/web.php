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

    Route::get('/daily-sales', [ReportsController::class, 'dailySales'])
        ->middleware('permission:reports.view')
        ->name('daily-sales');

    Route::get('/weekly-performance', [ReportsController::class, 'weeklyPerformance'])
        ->middleware('permission:reports.view')
        ->name('weekly-performance');

    Route::get('/low-stock-alert', [ReportsController::class, 'lowStockAlert'])
        ->middleware('permission:reports.view')
        ->name('low-stock-alert');

    Route::get('/stock', [ReportsController::class, 'stockReport'])
        ->middleware('permission:reports.view')
        ->name('stock');

    Route::get('/stock/detailed', [ReportsController::class, 'stockReportDetailed'])
        ->middleware('permission:reports.view')
        ->name('stock.detailed');

    Route::get('/stock/movement-trends', [ReportsController::class, 'stockMovementTrends'])
        ->middleware('permission:reports.view')
        ->name('stock.movement-trends');

    Route::get('/stock/reorder-recommendations', [ReportsController::class, 'stockReorderRecommendations'])
        ->middleware('permission:reports.view')
        ->name('stock.reorder-recommendations');

    Route::get('/stock/valuation', [ReportsController::class, 'stockValuation'])
        ->middleware('permission:reports.view')
        ->name('stock.valuation');
});

// Also add a shorter route name for easier access
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/daily-sales', [ReportsController::class, 'dailySales'])
        ->middleware('permission:reports.view')
        ->name('daily-sales');

    Route::get('/weekly-performance', [ReportsController::class, 'weeklyPerformance'])
        ->middleware('permission:reports.view')
        ->name('weekly-performance');

    Route::get('/low-stock-alert', [ReportsController::class, 'lowStockAlert'])
        ->middleware('permission:reports.view')
        ->name('low-stock-alert');
});
