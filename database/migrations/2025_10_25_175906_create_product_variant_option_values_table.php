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
        Schema::create('product_variant_option_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('option_id')->constrained('product_variant_options')->onDelete('cascade');
            $table->string('value', 100)->comment('Option value: Small, Red, Cotton, etc.');
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('store_id');
            $table->index('option_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variant_option_values');
    }
};
