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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            // Variant identification
            $table->string('sku')->unique()->nullable();
            $table->string('variant_name')->nullable()->comment('e.g., "Red / Large"');
            $table->string('barcode')->nullable();

            // Pricing (inherits from parent if NULL)
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('minimum_profit_margin', 5, 2)->nullable()->comment('Minimum profit margin percentage');
            $table->decimal('standard_profit_margin', 5, 2)->nullable()->comment('Standard profit margin percentage');
            $table->decimal('floor_price', 10, 2)->nullable()->comment('Calculated: cost_price + minimum_profit_margin');
            $table->decimal('target_price', 10, 2)->nullable()->comment('Calculated: cost_price + standard_profit_margin');

            // Stock (variant-specific)
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('reorder_level')->default(0);

            // Variant-specific attributes
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('image')->nullable()->comment('Variant-specific image');

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false)->comment('Mark one variant as default');

            $table->timestamps();

            // Indexes
            $table->index('store_id');
            $table->index('product_id');
            $table->index('sku');
            $table->index(['product_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
