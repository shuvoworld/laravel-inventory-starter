<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrder\Http\Resources\SalesOrderResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/sales-order')
    ->name('api.modules.sales-order.')
    ->group(function () {
        Route::get('/', function () {
            return SalesOrderResource::collection(SalesOrder::query()->latest('id')->paginate());
        })->middleware('permission:sales-order.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = SalesOrder::findOrFail($id);
            return new SalesOrderResource($model);
        })->middleware('permission:sales-order.view')->name('show');
    });
