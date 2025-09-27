<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_order_items', 'cost_price')) {
                Schema::table('sales_order_items', function (Blueprint $table) {
                    $table->decimal('cost_price', 10, 2)->default(0)->after('unit_price');
                });
            }
            if (!Schema::hasColumn('sales_order_items', 'cogs_amount')) {
                Schema::table('sales_order_items', function (Blueprint $table) {
                    $table->decimal('cogs_amount', 10, 2)->default(0);
                });
            }
            if (!Schema::hasColumn('sales_order_items', 'profit_amount')) {
                Schema::table('sales_order_items', function (Blueprint $table) {
                    $table->decimal('profit_amount', 10, 2)->default(0)->after('cogs_amount');
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'cogs_amount', 'profit_amount']);
        });
    }
};
