<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->decimal('discount_amount', 10, 2)->default(0)->after('total_price');
            $table->string('discount_type')->nullable()->after('discount_amount');
            $table->decimal('discount_rate', 5, 2)->nullable()->after('discount_type');
            $table->decimal('final_price', 12, 2)->default(0)->after('discount_rate');
            $table->text('discount_reason')->nullable()->after('final_price');
        });
    }

    public function down(): void
    {
        Schema::table('sales_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'discount_amount',
                'discount_type',
                'discount_rate',
                'final_price',
                'discount_reason'
            ]);
        });
    }
};