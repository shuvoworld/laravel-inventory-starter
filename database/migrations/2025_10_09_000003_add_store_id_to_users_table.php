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
        // Add store_id to users table if not exists
        if (!Schema::hasColumn('users', 'store_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('store_id')
                    ->nullable()
                    ->after('is_superadmin')
                    ->constrained('stores')
                    ->onDelete('cascade');

                $table->index('store_id');
            });
        }

        // Migrate data from store_users pivot to users.store_id
        if (Schema::hasTable('store_users')) {
            DB::statement('
                UPDATE users u
                INNER JOIN store_users su ON u.id = su.user_id
                SET u.store_id = su.store_id
                WHERE u.store_id IS NULL
            ');
        }

        // Drop store_users pivot table
        Schema::dropIfExists('store_users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate store_users pivot table
        Schema::create('store_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['store_id', 'user_id']);
        });

        // Migrate data back from users.store_id to store_users
        DB::statement('
            INSERT INTO store_users (store_id, user_id, created_at, updated_at)
            SELECT store_id, id, NOW(), NOW()
            FROM users
            WHERE store_id IS NOT NULL
        ');

        // Drop store_id from users
        if (Schema::hasColumn('users', 'store_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }
    }
};
