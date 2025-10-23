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
        $roles = [
            'admin' => $permissions, // admin gets all
            'store-admin' => [
                // Store admin gets basic access
                'users.view', 'users.create', 'users.edit', // Limited user management
                'types.view', 'types.create', 'types.edit', // Product types
                'blog-category.view', 'blog-category.create', 'blog-category.edit', // Categories
                'expense.view', 'expense.create', 'expense.edit', // Expenses
                'expense-category.view', 'expense-category.create', 'expense-category.edit', // Expense categories
            ],
            'editor' => [
                // As example, editor can manage Types except delete and manage Blog Categories (no delete)
                'types.view', 'types.create', 'types.edit',
                'blog-category.view', 'blog-category.create', 'blog-category.edit',
            ],
            'viewer' => [
                // read-only access
                'types.view',
                'blog-category.view',
            ],
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::findOrCreate($roleName, $guard);
            $role->syncPermissions($perms);
        }

        // Optionally create an admin user if none exists
        if (!User::where('email', 'admin@example.com')->exists()) {
            $admin = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'password', // Will be hashed by cast
            ]);
            $admin->assignRole('admin');
        }
    }
}
