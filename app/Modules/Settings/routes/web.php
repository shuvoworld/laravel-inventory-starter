<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Settings\Http\Controllers\SettingsController;

Route::middleware(['auth'])->prefix('modules/settings')->name('modules.settings.')->group(function () {
    Route::get('/', [SettingsController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('index');

    Route::put('/', [SettingsController::class, 'update'])
        ->middleware('permission:settings.edit')
        ->name('update');
});
