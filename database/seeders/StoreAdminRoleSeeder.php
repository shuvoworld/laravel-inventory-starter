<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StoreAdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure default guard
        $guard = config('auth.defaults.guard', 'web');

        // Create Super Admin role (all access)
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => $guard]);

        // Create Admin role (all access)
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);

        // Create store-admin role (full store access)
        $storeAdminRole = Role::firstOrCreate(['name' => 'store-admin', 'guard_name' => $guard]);

        // Create store-user role (sales-only access)
        $storeUserRole = Role::firstOrCreate(['name' => 'store-user', 'guard_name' => $guard]);

        // Define ALL permissions for super-admin and admin (complete access)
        $allPermissions = [
            // User management permissions
            'users.view', 'users.create', 'users.edit', 'users.delete',

            // Product permissions
            'products.view', 'products.create', 'products.edit', 'products.delete',

            // Product Category permissions
            'product-category.view', 'product-category.create', 'product-category.edit', 'product-category.delete',

            // Brand permissions
            'brand.view', 'brand.create', 'brand.edit', 'brand.delete',

            // Attribute Set permissions
            'attribute-set.view', 'attribute-set.create', 'attribute-set.edit', 'attribute-set.delete',

            // Product Attribute permissions
            'product-attribute.view', 'product-attribute.create', 'product-attribute.edit', 'product-attribute.delete',

            // Customer permissions
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',

            // Sales Order permissions
            'sales-order.view', 'sales-order.create', 'sales-order.edit', 'sales-order.delete',

            // Sales Order Item permissions
            'sales-order-item.view', 'sales-order-item.create', 'sales-order-item.edit', 'sales-order-item.delete',

            // Purchase Order permissions
            'purchase-order.view', 'purchase-order.create', 'purchase-order.edit', 'purchase-order.delete',

            // Purchase Order Item permissions
            'purchase-order-item.view', 'purchase-order-item.create', 'purchase-order-item.edit', 'purchase-order-item.delete',

            // Supplier permissions
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',

            // Stock Movement permissions
            'stock-movement.view', 'stock-movement.create', 'stock-movement.edit', 'stock-movement.delete', 'stock-movement.reconcile',

            // Reports permissions
            'reports.view',

            // Settings permissions
            'settings.view', 'settings.edit',

            // Store Settings permissions
            'store-settings.view', 'store-settings.edit',

            // Operating Expenses permissions
            'operating-expense.view', 'operating-expense.create', 'operating-expense.edit', 'operating-expense.delete',

            // Expense Management permissions
            'expense.view', 'expense.create', 'expense.edit', 'expense.delete',

            // Expense Category permissions
            'expense-category.view', 'expense-category.create', 'expense-category.edit', 'expense-category.delete',

            // Sales Return permissions
            'sales-return.view', 'sales-return.create', 'sales-return.edit', 'sales-return.delete',

            // Purchase Return permissions
            'purchase-return.view', 'purchase-return.create', 'purchase-return.edit', 'purchase-return.delete',

            // Types permissions
            'types.view', 'types.create', 'types.edit', 'types.delete',

            // Blog Category permissions
            'blog-category.view', 'blog-category.create', 'blog-category.edit', 'blog-category.delete',

            // Roles & Permissions
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'permissions.view', 'permissions.create', 'permissions.edit', 'permissions.delete',

            // Point of Sale
            'pos.view', 'pos.create',
        ];

        // Store Admin gets same as super admin but for their store only
        $storeAdminPermissions = $allPermissions;

        // Define permissions for store-user (sales and basic stock management)
        $storeUserPermissions = [
            'sales-order.view', 'sales-order.create', 'sales-order.edit', 'sales-order.delete',
            'customers.view', 'customers.create',
            'products.view',
            'stock-movement.view', 'stock-movement.create',
            'reports.view',
            'pos.view', 'pos.create',
        ];

        // Create all permissions
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        // Sync permissions for Super Admin (ALL permissions)
        $superAdminRole->syncPermissions($allPermissions);

        // Sync permissions for Admin (ALL permissions)
        $adminRole->syncPermissions($allPermissions);

        // Sync permissions for Store Admin (ALL permissions for store management)
        $storeAdminRole->syncPermissions($storeAdminPermissions);

        // Sync permissions for Store User (limited permissions)
        $storeUserRole->syncPermissions($storeUserPermissions);

        $this->command->info('✓ Roles and permissions created successfully!');
        $this->command->info('✓ Super Admin permissions: ' . $superAdminRole->permissions->count());
        $this->command->info('✓ Admin permissions: ' . $adminRole->permissions->count());
        $this->command->info('✓ Store Admin permissions: ' . $storeAdminRole->permissions->count());
        $this->command->info('✓ Store User permissions: ' . $storeUserRole->permissions->count());
    }
}