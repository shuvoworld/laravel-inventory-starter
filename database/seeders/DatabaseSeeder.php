<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions (also creates an admin user)
        $this->call([
            RolePermissionSeeder::class,
        ]);

        // Optionally create a demo viewer user
        if (!User::where('email', 'viewer@example.com')->exists()) {
            $viewer = User::factory()->create([
                'name' => 'Viewer User',
                'email' => 'viewer@example.com',
                'password' => 'password',
            ]);
            $viewer->assignRole('viewer');
        }
    }
}
