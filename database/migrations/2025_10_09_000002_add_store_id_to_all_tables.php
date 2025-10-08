<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add store_id to all module tables
        $tables = [
            'customers',
            'operating_expenses',
            'products',
            'purchase_orders',
            'purchase_order_items',
            'purchase_returns',
            'purchase_return_items',
            'sales_orders',
            'sales_order_items',
            'sales_returns',
            'sales_return_items',
            'settings',
            'stock_movements',
            'store_settings',
            'types',
            'suppliers',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('store_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('stores')
                        ->onDelete('cascade');

                    $table->index('store_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'customers',
            'operating_expenses',
            'products',
            'purchase_orders',
            'purchase_order_items',
            'purchase_returns',
            'purchase_return_items',
            'sales_orders',
            'sales_order_items',
            'sales_returns',
            'sales_return_items',
            'settings',
            'stock_movements',
            'store_settings',
            'types',
            'suppliers',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['store_id']);
                    $table->dropColumn('store_id');
                });
            }
        }
    }
};
