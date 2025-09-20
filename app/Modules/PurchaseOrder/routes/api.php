<?php

use Illuminate\Support\Facades\Route;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\PurchaseOrder\Http\Resources\PurchaseOrderResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/purchase-order')
    ->name('api.modules.purchase-order.')
    ->group(function () {
        Route::get('/', function () {
            return PurchaseOrderResource::collection(PurchaseOrder::query()->latest('id')->paginate());
        })->middleware('permission:purchase-order.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = PurchaseOrder::findOrFail($id);
            return new PurchaseOrderResource($model);
        })->middleware('permission:purchase-order.view')->name('show');
    });
