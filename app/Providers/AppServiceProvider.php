<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define morph maps for polymorphic relationships
        Relation::morphMap([
            'purchase_order' => \App\Modules\PurchaseOrder\Models\PurchaseOrder::class,
            'sales_order' => \App\Modules\SalesOrder\Models\SalesOrder::class,
            'purchase_return' => \App\Modules\PurchaseReturn\Models\PurchaseReturn::class,
            'sales_return' => \App\Modules\SalesReturn\Models\SalesReturn::class,
            'stock_adjustment' => \App\Modules\StockAdjustment\Models\StockAdjustment::class,
            'stock_transfer' => \App\Modules\StockTransfer\Models\StockTransfer::class,
            'user' => \App\Models\User::class,
        ]);

        // Superadmin gate - bypass all permission checks for superadmins
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
                return true;
            }
        });
    }
}
