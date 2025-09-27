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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('subtotal')->default(0)->change();
            $table->decimal('tax_amount')->default(0)->change();
            $table->decimal('discount_amount')->default(0)->change();
            $table->decimal('total_amount')->default(0)->change();
            $table->decimal('paid_amount')->default(0)->change();
            $table->decimal('change_amount')->default(0)->change();
            $table->decimal('cogs_amount')->default(0)->change();
            $table->decimal('profit_amount')->default(0)->change();
            $table->decimal('discount_rate')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('subtotal')->change();
            $table->decimal('tax_amount')->change();
            $table->decimal('discount_amount')->change();
            $table->decimal('total_amount')->change();
            $table->decimal('paid_amount')->change();
            $table->decimal('change_amount')->change();
            $table->decimal('cogs_amount')->change();
            $table->decimal('profit_amount')->change();
            $table->decimal('discount_rate')->change();
        });
    }
};