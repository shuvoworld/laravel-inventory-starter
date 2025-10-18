<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ProductAttribute\Models\ProductAttribute;
use App\Modules\ProductAttribute\Http\Resources\ProductAttributeResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/product-attribute')
    ->name('api.modules.product-attribute.')
    ->group(function () {
        Route::get('/', function () {
            return ProductAttributeResource::collection(ProductAttribute::query()->latest('id')->paginate());
        })->middleware('permission:product-attribute.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = ProductAttribute::findOrFail($id);
            return new ProductAttributeResource($model);
        })->middleware('permission:product-attribute.view')->name('show');
    });
