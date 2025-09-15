<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            // Kebab-case name, e.g. "contact", "blog-category"
            $table->string('name')->unique();
            // StudlyCase namespace, e.g. "Contact"
            $table->string('namespace');
            // Optional human title to show in UIs
            $table->string('title')->nullable();
            // Absolute path on disk for reference/debug
            $table->string('path');
            // Activation flag
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
