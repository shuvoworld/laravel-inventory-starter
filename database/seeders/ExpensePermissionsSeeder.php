<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class ExpensePermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing permissions
        DB::table('permissions')->where('name', 'like', 'expense%')->delete();

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
            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($expensePermissions as $name => $description) {
            Permission::create([
                'name' => $name,
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Expense permissions seeded successfully.\n";
    }
}