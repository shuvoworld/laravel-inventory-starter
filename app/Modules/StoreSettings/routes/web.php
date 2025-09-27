<?php

use App\Modules\StoreSettings\Http\Controllers\StoreSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('modules.store-settings.')->group(function () {
    Route::get('store-settings', [StoreSettingsController::class, 'index'])->name('index');
    Route::put('store-settings', [StoreSettingsController::class, 'update'])->name('update');
    Route::post('store-settings/reset', [StoreSettingsController::class, 'reset'])->name('reset');
    Route::post('store-settings/clear-cache', [StoreSettingsController::class, 'clearCache'])->name('clear-cache');
    Route::get('store-settings/export', [StoreSettingsController::class, 'export'])->name('export');
    Route::post('store-settings/import', [StoreSettingsController::class, 'import'])->name('import');
});