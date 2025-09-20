<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Reports\Models\Reports;
use App\Modules\Reports\Http\Resources\ReportsResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/reports')
    ->name('api.modules.reports.')
    ->group(function () {
        Route::get('/', function () {
            return ReportsResource::collection(Reports::query()->latest('id')->paginate());
        })->middleware('permission:reports.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = Reports::findOrFail($id);
            return new ReportsResource($model);
        })->middleware('permission:reports.view')->name('show');
    });
