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
        Schema::table('product_attributes', function (Blueprint $table) {
            // Only add attribute_set_id since store_id already exists
            $table->foreignId('attribute_set_id')->nullable()->after('store_id')->constrained('attribute_sets')->onDelete('set null');
            $table->index('attribute_set_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_attributes', function (Blueprint $table) {
            $table->dropForeign(['attribute_set_id']);
            $table->dropIndex(['attribute_set_id']);
            $table->dropColumn('attribute_set_id');
        });
    }
};
