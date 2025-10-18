<?php

use Illuminate\Support\Facades\Route;
use App\Modules\AttributeSet\Http\Controllers\AttributeSetController;

Route::middleware(['auth'])->prefix('modules/attribute-set')->name('modules.attribute-set.')->group(function () {
    Route::get('/', [AttributeSetController::class, 'index'])
        ->middleware('permission:attribute-set.view')
        ->name('index');

    Route::get('/data', [AttributeSetController::class, 'data'])
        ->middleware('permission:attribute-set.view')
        ->name('data');

    Route::get('/create', [AttributeSetController::class, 'create'])
        ->middleware('permission:attribute-set.create')
        ->name('create');

    Route::post('/', [AttributeSetController::class, 'store'])
        ->middleware('permission:attribute-set.create')
        ->name('store');

    Route::get('/{id}', [AttributeSetController::class, 'show'])
        ->middleware('permission:attribute-set.view')
        ->name('show');

    Route::get('/{id}/edit', [AttributeSetController::class, 'edit'])
        ->middleware('permission:attribute-set.edit')
        ->name('edit');

    Route::put('/{id}', [AttributeSetController::class, 'update'])
        ->middleware('permission:attribute-set.edit')
        ->name('update');

    Route::delete('/{id}', [AttributeSetController::class, 'destroy'])
        ->middleware('permission:attribute-set.delete')
        ->name('destroy');
});
