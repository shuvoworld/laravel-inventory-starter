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
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->decimal('cogs_amount', 10, 2)->default(0)->after('total_amount');
            $table->decimal('profit_amount', 10, 2)->default(0)->after('cogs_amount');
        });

        // Update existing records
        DB::statement('UPDATE sales_orders SET total_amount = COALESCE(total_amount, 0), cogs_amount = 0, profit_amount = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['cogs_amount', 'profit_amount']);
        });
    }
};