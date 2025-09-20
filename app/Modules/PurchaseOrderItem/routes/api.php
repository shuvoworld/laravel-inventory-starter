<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\PurchaseOrderItem\Http\Resources\PurchaseOrderItemResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/purchase-order-item')
    ->name('api.modules.purchase-order-item.')
    ->group(function () {
        Route::get('/', function () {
            return PurchaseOrderItemResource::collection(PurchaseOrderItem::query()->latest('id')->paginate());
        })->middleware('permission:purchase-order-item.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = PurchaseOrderItem::findOrFail($id);
            return new PurchaseOrderItemResource($model);
        })->middleware('permission:purchase-order-item.view')->name('show');
    });
