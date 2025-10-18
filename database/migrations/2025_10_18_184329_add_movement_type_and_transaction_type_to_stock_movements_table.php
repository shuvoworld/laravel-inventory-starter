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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('movement_type')->default('in')->after('product_id');
            $table->string('transaction_type')->nullable()->after('movement_type');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('store_id');

            // For existing records, map the old 'type' field to 'movement_type'
            $table->string('old_type')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['movement_type', 'transaction_type', 'user_id', 'old_type']);
        });
    }
};
