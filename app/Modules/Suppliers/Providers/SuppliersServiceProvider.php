<?php

namespace App\Modules\Suppliers\Providers;

use Illuminate\Support\ServiceProvider;

class SuppliersServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Register views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'Suppliers');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}