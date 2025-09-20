<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Settings\Models\Settings;
use App\Modules\Settings\Http\Resources\SettingsResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/settings')
    ->name('api.modules.settings.')
    ->group(function () {
        Route::get('/', function () {
            return SettingsResource::collection(Settings::query()->latest('id')->paginate());
        })->middleware('permission:settings.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = Settings::findOrFail($id);
            return new SettingsResource($model);
        })->middleware('permission:settings.view')->name('show');
    });
