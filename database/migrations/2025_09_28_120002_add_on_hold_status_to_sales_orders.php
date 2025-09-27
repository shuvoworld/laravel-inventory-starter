<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'on_hold', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending')->change();
            $table->text('hold_reason')->nullable()->after('reference_number');
            $table->timestamp('hold_date')->nullable()->after('hold_reason');
            $table->timestamp('release_date')->nullable()->after('hold_date');
            $table->foreignId('held_by')->nullable()->after('release_date')->constrained('users');
            $table->foreignId('released_by')->nullable()->after('held_by')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending')->change();
            $table->dropColumn([
                'hold_reason',
                'hold_date',
                'release_date',
                'held_by',
                'released_by'
            ]);
        });
    }
};