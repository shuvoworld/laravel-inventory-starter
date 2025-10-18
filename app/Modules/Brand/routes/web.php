<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Brand\Http\Controllers\BrandController;

Route::middleware(['auth'])->prefix('modules/brand')->name('modules.brand.')->group(function () {
    Route::get('/', [BrandController::class, 'index'])
        ->middleware('permission:brand.view')
        ->name('index');

    Route::get('/data', [BrandController::class, 'data'])
        ->middleware('permission:brand.view')
        ->name('data');

    Route::get('/create', [BrandController::class, 'create'])
        ->middleware('permission:brand.create')
        ->name('create');

    Route::post('/', [BrandController::class, 'store'])
        ->middleware('permission:brand.create')
        ->name('store');

    Route::get('/{id}', [BrandController::class, 'show'])
        ->middleware('permission:brand.view')
        ->name('show');

    Route::get('/{id}/edit', [BrandController::class, 'edit'])
        ->middleware('permission:brand.edit')
        ->name('edit');

    Route::put('/{id}', [BrandController::class, 'update'])
        ->middleware('permission:brand.edit')
        ->name('update');

    Route::delete('/{id}', [BrandController::class, 'destroy'])
        ->middleware('permission:brand.delete')
        ->name('destroy');
});
