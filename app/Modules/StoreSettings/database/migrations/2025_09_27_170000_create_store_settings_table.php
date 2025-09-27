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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, file
            $table->string('group')->default('general'); // general, company, currency, appearance, etc.
            $table->string('label');
            $table->text('description')->nullable();
            $table->json('options')->nullable(); // For select dropdowns, validation rules, etc.
            $table->boolean('is_public')->default(false); // Can be accessed in frontend
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'sort_order']);
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};