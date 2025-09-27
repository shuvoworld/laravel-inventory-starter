<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->after('notes');
            $table->string('payment_status')->default('pending')->after('payment_method');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');
            $table->string('discount_type')->nullable()->after('discount_amount');
            $table->decimal('discount_rate', 5, 2)->nullable()->after('discount_type');
            $table->text('discount_reason')->nullable()->after('discount_rate');
            $table->decimal('change_amount', 12, 2)->default(0)->after('paid_amount');
            $table->timestamp('payment_date')->nullable()->after('change_amount');
            $table->string('reference_number')->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'paid_amount',
                'discount_type',
                'discount_rate',
                'discount_reason',
                'change_amount',
                'payment_date',
                'reference_number'
            ]);
        });
    }
};