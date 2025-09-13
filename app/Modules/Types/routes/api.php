<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Types\Models\Type;
use App\Modules\Types\Http\Resources\TypeResource;

Route::middleware(['api'])
    ->prefix('api/modules/types')
    ->name('api.modules.types.')
    ->group(function () {
        Route::get('/', function () {
            return TypeResource::collection(Type::query()->latest('id')->paginate());
        })->middleware('permission:types.view')->name('index');

        Route::get('/{id}', function (int $id) {
            return new TypeResource(Type::findOrFail($id));
        })->middleware('permission:types.view')->name('show');
    });
