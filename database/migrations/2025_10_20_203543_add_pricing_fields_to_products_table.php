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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('minimum_profit_margin', 5, 2)->default(7.5)->after('cost_price')->comment('Minimum profit margin percentage');
            $table->decimal('standard_profit_margin', 5, 2)->default(7.5)->after('minimum_profit_margin')->comment('Standard profit margin percentage');
            $table->decimal('floor_price', 10, 2)->nullable()->after('standard_profit_margin')->comment('Calculated: cost_price + minimum_profit_margin');
            $table->decimal('target_price', 10, 2)->nullable()->after('floor_price')->comment('Calculated: cost_price + standard_profit_margin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['minimum_profit_margin', 'standard_profit_margin', 'floor_price', 'target_price']);
        });
    }
};
