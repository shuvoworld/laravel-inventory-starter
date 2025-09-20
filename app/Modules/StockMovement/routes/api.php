<?php

use Illuminate\Support\Facades\Route;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\StockMovement\Http\Resources\StockMovementResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/stock-movement')
    ->name('api.modules.stock-movement.')
    ->group(function () {
        Route::get('/', function () {
            return StockMovementResource::collection(StockMovement::query()->latest('id')->paginate());
        })->middleware('permission:stock-movement.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = StockMovement::findOrFail($id);
            return new StockMovementResource($model);
        })->middleware('permission:stock-movement.view')->name('show');
    });
