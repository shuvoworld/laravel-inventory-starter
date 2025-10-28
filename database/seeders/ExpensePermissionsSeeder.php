<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class ExpensePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        // Expense Category permissions
        $categoryPermissions = [
            'expense-category.view' => 'View Expense Categories',
            'expense-category.create' => 'Create Expense Categories',
            'expense-category.edit' => 'Edit Expense Categories',
            'expense-category.delete' => 'Delete Expense Categories',
        ];

        // Expense permissions
        $expensePermissions = [
            'expense.view' => 'View Expenses',
            'expense.create' => 'Create Expenses',
            'expense.edit' => 'Edit Expenses',
            'expense.delete' => 'Delete Expenses',
        ];

        $allExpensePermissions = [];

        foreach ($categoryPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
            $allExpensePermissions[] = $name;
        }

        foreach ($expensePermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard]
            );
            $allExpensePermissions[] = $name;
        }

        // Assign these permissions to super-admin and admin roles
        $superAdminRole = Role::where('name', 'super-admin')->where('guard_name', $guard)->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($allExpensePermissions);
        }

        $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($allExpensePermissions);
        }

        $storeAdminRole = Role::where('name', 'store-admin')->where('guard_name', $guard)->first();
        if ($storeAdminRole) {
            $storeAdminRole->givePermissionTo($allExpensePermissions);
        }

        echo "Expense permissions seeded successfully.\n";
    }
}