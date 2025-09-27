<?php

namespace App\Modules\StoreSettings\Providers;

use Illuminate\Support\ServiceProvider;

class StoreSettingsServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'StoreSettings');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}