<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // List of all tables with store_id column
        $tables = [
            'users',
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

        // Update all store_id to 1 for existing data
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'store_id')) {
                DB::table($table)
                    ->whereNull('store_id')
                    ->orWhere('store_id', '!=', 1)
                    ->update(['store_id' => 1]);
            }
        }

        // Make sure store with id=1 exists
        if (Schema::hasTable('stores')) {
            $storeExists = DB::table('stores')->where('id', 1)->exists();

            if (!$storeExists) {
                DB::table('stores')->insert([
                    'id' => 1,
                    'name' => 'Main Store',
                    'slug' => 'main-store',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this migration
        // Data migration is one-way
    }
};
