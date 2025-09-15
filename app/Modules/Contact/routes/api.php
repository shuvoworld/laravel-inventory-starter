<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Contact\Models\Contact;
use App\Modules\Contact\Http\Resources\ContactResource;

// Module API routes (explicitly prefixed with /api and using the 'api' middleware)
Route::middleware(['api'])
    ->prefix('api/modules/contact')
    ->name('api.modules.contact.')
    ->group(function () {
        Route::get('/', function () {
            return ContactResource::collection(Contact::query()->latest('id')->paginate());
        })->middleware('permission:contact.view')->name('index');

        Route::get('/{id}', function (int $id) {
            $model = Contact::findOrFail($id);
            return new ContactResource($model);
        })->middleware('permission:contact.view')->name('show');
    });
