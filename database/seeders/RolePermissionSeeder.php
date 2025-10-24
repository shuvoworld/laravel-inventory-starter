<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure default guard is web
        $guard = config('auth.defaults.guard', 'web');

        // Clean up: remove deprecated module permissions for posts.* and authors.*
        $toRemove = [
            'posts.view', 'posts.create', 'posts.edit', 'posts.delete',
            'authors.view', 'authors.create', 'authors.edit', 'authors.delete',
        ];
        foreach ($toRemove as $permName) {
            if ($perm = Permission::where('name', $permName)->where('guard_name', $guard)->first()) {
                $perm->roles()->detach();
                $perm->delete();
            }
        }

        // Define permissions (CRUD per module)
        $permissions = [
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Types module
            'types.view', 'types.create', 'types.edit', 'types.delete',
            // Blog Category module
            'blog-category.view', 'blog-category.create', 'blog-category.edit', 'blog-category.delete',
            // Expense Management
            'expense.view', 'expense.create', 'expense.edit', 'expense.delete',
            // Expense Category Management
            'expense-category.view', 'expense-category.create', 'expense-category.edit', 'expense-category.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, $guard);
        }

        // Define roles and attach permissions
        // NOTE: store-admin and store-user roles are managed by StoreAdminRoleSeeder
        // DO NOT override them here to avoid permission conflicts
        $roles = [
            'editor' => [
                // Editor can manage Types except delete and manage Blog Categories (no delete)
                'types.view', 'types.create', 'types.edit',
                'blog-category.view', 'blog-category.create', 'blog-category.edit',
            ],
            'viewer' => [
                // Read-only access
                'types.view',
                'blog-category.view',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, $guard);
            // Only sync if role doesn't have permissions already (don't override)
            if ($role->permissions->isEmpty()) {
                $role->syncPermissions($perms);
            }
        }

        // Optionally create an admin user if none exists
        if (!User::where('email', 'admin@example.com')->exists()) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'password', // Will be hashed by cast
            ]);
            // Assign admin role if it exists, otherwise super-admin
            if (Role::where('name', 'admin')->exists()) {
                $admin->assignRole('admin');
            } elseif (Role::where('name', 'super-admin')->exists()) {
                $admin->assignRole('super-admin');
            }
        }

        $this->command->info('✓ Additional roles and permissions seeded (editor, viewer)');
        $this->command->info('✓ NOTE: Run StoreAdminRoleSeeder for store-admin and store-user roles');
    }
}
