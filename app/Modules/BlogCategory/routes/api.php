<?php

use Illuminate\Support\Facades\Route;
use App\Modules\BlogCategory\Models\BlogCategory;
use App\Modules\BlogCategory\Http\Resources\BlogCategoryResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/blog-category')
    ->name('api.modules.blog-category.')
    ->group(function () {
        Route::get('/', function () {
            return BlogCategoryResource::collection(BlogCategory::query()->latest('id')->paginate());
        })->middleware('permission:blog-category.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = BlogCategory::findOrFail($id);
            return new BlogCategoryResource($model);
        })->middleware('permission:blog-category.view')->name('show');
    });
