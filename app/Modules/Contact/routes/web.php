<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Contact\Http\Controllers\ContactController;

Route::middleware(['auth'])->prefix('modules/contact')->name('modules.contact.')->group(function () {
    Route::get('/', [ContactController::class, 'index'])
        ->middleware('permission:contact.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [ContactController::class, 'data'])
        ->middleware('permission:contact.view')
        ->name('data');

    Route::get('/create', [ContactController::class, 'create'])
        ->middleware('permission:contact.create')
        ->name('create');

    Route::post('/', [ContactController::class, 'store'])
        ->middleware('permission:contact.create')
        ->name('store');

    Route::get('/{id}', [ContactController::class, 'show'])
        ->middleware('permission:contact.view')
        ->name('show');

    Route::get('/{id}/edit', [ContactController::class, 'edit'])
        ->middleware('permission:contact.edit')
        ->name('edit');

    Route::put('/{id}', [ContactController::class, 'update'])
        ->middleware('permission:contact.edit')
        ->name('update');

    Route::delete('/{id}', [ContactController::class, 'destroy'])
        ->middleware('permission:contact.delete')
        ->name('destroy');
});
