<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ExpensePermissionsSeeder extends Seeder
{
    public function run(): void
    {
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

        foreach ($categoryPermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        foreach ($expensePermissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        echo "Expense permissions seeded successfully.\n";
    }
}