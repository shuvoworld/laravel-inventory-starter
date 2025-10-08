<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Modules\Stores\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Demo Store
        $demoStore = Store::firstOrCreate(
            ['slug' => 'demo-store'],
            [
                'name' => 'Demo Store',
                'is_active' => true,
            ]
        );

        // Create Store Admin Demo User
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Store Admin',
                'password' => Hash::make('password'),
                'store_id' => $demoStore->id,
                'is_active' => 1,
            ]
        );
        $adminUser->assignRole('store-admin');

        // Create Store User Demo User
        $storeUser = User::firstOrCreate(
            ['email' => 'user@demo.com'],
            [
                'name' => 'Store User',
                'password' => Hash::make('password'),
                'store_id' => $demoStore->id,
                'is_active' => 1,
            ]
        );
        $storeUser->assignRole('store-user');

        $this->command->info('Demo users created successfully!');
        $this->command->info('Store Admin: admin@demo.com / password');
        $this->command->info('Store User: user@demo.com / password');
    }
}
