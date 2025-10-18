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
        // Create store-admin role if it doesn't exist
        $storeAdminRole = Role::firstOrCreate(['name' => 'store-admin']);
        // Create store-user role (sales-only access)
        $storeUserRole = Role::firstOrCreate(['name' => 'store-user']);
        // Define permissions for store-admin (full access)
        $storeAdminPermissions = [
            // User management permissions
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            // Product permissions
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            // Brand permissions
            'brand.view',
            'brand.create',
            'brand.edit',
            'brand.delete',
            // Attribute Set permissions
            'attribute-set.view',
            'attribute-set.create',
            'attribute-set.edit',
            'attribute-set.delete',
            // Product Attribute permissions
            'product-attribute.view',
            'product-attribute.create',
            'product-attribute.edit',
            'product-attribute.delete',
            // Customer permissions
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',
            // Sales Order permissions
            'sales-order.view',
            'sales-order.create',
            'sales-order.edit',
            'sales-order.delete',
            // Purchase Order permissions
            'purchase-order.view',
            'purchase-order.create',
            'purchase-order.edit',
            'purchase-order.delete',
            // Supplier permissions
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            // Stock Movement permissions
            'stock-movement.view',
            // Reports permissions
            'reports.view',
            // Settings permissions
            'settings.view',
            'settings.edit',
            // Operating Expenses permissions
            'operating-expense.view',
            'operating-expense.create',
            'operating-expense.edit',
            'operating-expense.delete',
            // Expense Management permissions
            'expense.view',
            'expense.create',
            'expense.edit',
            'expense.delete',
            // Expense Category permissions
            'expense-category.view',
            'expense-category.create',
            'expense-category.edit',
            'expense-category.delete',
            // Sales Return permissions
            'sales-return.view',
            'sales-return.create',
            'sales-return.edit',
            'sales-return.delete',
            // Purchase Return permissions
            'purchase-return.view',
            'purchase-return.create',
            'purchase-return.edit',
            'purchase-return.delete',
        ];

        // Define permissions for store-user (sales-only)
        $storeUserPermissions = [
            // Sales Order permissions only
            'sales-order.view',
            'sales-order.create',
            'sales-order.edit',
            'sales-order.delete',
            // Customer view for sales
            'customers.view',
            // Product view for sales
            'products.view',
            // Reports permissions for stock monitoring
            'reports.view',
        ];

        // Create all permissions and assign to store-admin
        foreach ($storeAdminPermissions as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            // Assign permission to store-admin role if not already assigned
            if (!$storeAdminRole->hasPermissionTo($perm)) {
                $storeAdminRole->givePermissionTo($perm);
            }
        }

        // Create and assign permissions to store-user
        foreach ($storeUserPermissions as $permission) {
            $perm = Permission::firstOrCreate(['name' => $permission]);
            // Assign permission to store-user role if not already assigned
            if (!$storeUserRole->hasPermissionTo($perm)) {
                $storeUserRole->givePermissionTo($perm);
            }
        }

        $this->command->info('Store-admin and store-user roles created successfully!');
        $this->command->info('Store-admin permissions: ' . $storeAdminRole->permissions->count());
        $this->command->info('Store-user permissions: ' . $storeUserRole->permissions->count());
    }
}