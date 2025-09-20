<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\SalesOrderItem\Http\Resources\SalesOrderItemResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/sales-order-item')
    ->name('api.modules.sales-order-item.')
    ->group(function () {
        Route::get('/', function () {
            return SalesOrderItemResource::collection(SalesOrderItem::query()->latest('id')->paginate());
        })->middleware('permission:sales-order-item.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = SalesOrderItem::findOrFail($id);
            return new SalesOrderItemResource($model);
        })->middleware('permission:sales-order-item.view')->name('show');
    });
