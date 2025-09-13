<?php

use Illuminate\Support\Facades\Route;
use App\Modules\BlogCategory\Http\Controllers\BlogCategoryController;

Route::middleware(['auth'])->prefix('modules/blog-category')->name('modules.blog-category.')->group(function () {
    Route::get('/', [BlogCategoryController::class, 'index'])
        ->middleware('permission:blog-category.view')
        ->name('index');

    // Server-side data endpoint for DataTables
    Route::get('/data', [BlogCategoryController::class, 'data'])
        ->middleware('permission:blog-category.view')
        ->name('data');

    Route::get('/create', [BlogCategoryController::class, 'create'])
        ->middleware('permission:blog-category.create')
        ->name('create');

    Route::post('/', [BlogCategoryController::class, 'store'])
        ->middleware('permission:blog-category.create')
        ->name('store');

    Route::get('/{id}', [BlogCategoryController::class, 'show'])
        ->middleware('permission:blog-category.view')
        ->name('show');

    Route::get('/{id}/edit', [BlogCategoryController::class, 'edit'])
        ->middleware('permission:blog-category.edit')
        ->name('edit');

    Route::put('/{id}', [BlogCategoryController::class, 'update'])
        ->middleware('permission:blog-category.edit')
        ->name('update');

    Route::delete('/{id}', [BlogCategoryController::class, 'destroy'])
        ->middleware('permission:blog-category.delete')
        ->name('destroy');
});
