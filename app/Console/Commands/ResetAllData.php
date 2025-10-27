<?php

namespace App\Console\Commands;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Products\Models\Product;
use App\Modules\Suppliers\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetAllData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-all-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all transactional data: sales, purchases, returns, stock movements, expenses, and delete all products (for testing)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('This will permanently delete ALL sales orders, purchase orders, and ALL products. Are you sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        if (!$this->confirm('This action cannot be undone. Are you absolutely sure?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Starting data reset process...');

        try {
            DB::beginTransaction();

            // Disable foreign key checks to allow truncation
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $this->line('✓ Foreign key checks disabled');

            // Count records before deletion
            $salesReturnItemCount = 0;
            $salesReturnCount = 0;
            $purchaseReturnItemCount = 0;
            $purchaseReturnCount = 0;
            $salesOrderItemCount = 0;
            $salesOrderCount = 0;
            $purchaseOrderItemCount = 0;
            $purchaseOrderCount = 0;
            $stockMovementCount = 0;
            $operatingExpenseCount = 0;
            $productCount = 0;

            // Reset Sales Return Items first
            if (Schema::hasTable('sales_return_items')) {
                $this->info('Resetting Sales Return Items...');
                $salesReturnItemCount = DB::table('sales_return_items')->count();
                DB::table('sales_return_items')->truncate();
                $this->line("✓ Deleted {$salesReturnItemCount} sales return items");
            }

            // Reset Sales Returns
            if (Schema::hasTable('sales_returns')) {
                $this->info('Resetting Sales Returns...');
                $salesReturnCount = DB::table('sales_returns')->count();
                DB::table('sales_returns')->truncate();
                $this->line("✓ Deleted {$salesReturnCount} sales returns");
            }

            // Reset Purchase Return Items first
            if (Schema::hasTable('purchase_return_items')) {
                $this->info('Resetting Purchase Return Items...');
                $purchaseReturnItemCount = DB::table('purchase_return_items')->count();
                DB::table('purchase_return_items')->truncate();
                $this->line("✓ Deleted {$purchaseReturnItemCount} purchase return items");
            }

            // Reset Purchase Returns
            if (Schema::hasTable('purchase_returns')) {
                $this->info('Resetting Purchase Returns...');
                $purchaseReturnCount = DB::table('purchase_returns')->count();
                DB::table('purchase_returns')->truncate();
                $this->line("✓ Deleted {$purchaseReturnCount} purchase returns");
            }

            // Reset Sales Order Items first
            if (Schema::hasTable('sales_order_items')) {
                $this->info('Resetting Sales Order Items...');
                $salesOrderItemCount = DB::table('sales_order_items')->count();
                DB::table('sales_order_items')->truncate();
                $this->line("✓ Deleted {$salesOrderItemCount} sales order items");
            }

            // Reset Sales Orders
            if (Schema::hasTable('sales_orders')) {
                $this->info('Resetting Sales Orders...');
                $salesOrderCount = DB::table('sales_orders')->count();
                DB::table('sales_orders')->truncate();
                $this->line("✓ Deleted {$salesOrderCount} sales orders");
            }

            // Reset Purchase Order Items first
            if (Schema::hasTable('purchase_order_items')) {
                $this->info('Resetting Purchase Order Items...');
                $purchaseOrderItemCount = DB::table('purchase_order_items')->count();
                DB::table('purchase_order_items')->truncate();
                $this->line("✓ Deleted {$purchaseOrderItemCount} purchase order items");
            }

            // Reset Purchase Orders
            if (Schema::hasTable('purchase_orders')) {
                $this->info('Resetting Purchase Orders...');
                $purchaseOrderCount = DB::table('purchase_orders')->count();
                DB::table('purchase_orders')->truncate();
                $this->line("✓ Deleted {$purchaseOrderCount} purchase orders");
            }

            // Reset Stock Movements
            if (Schema::hasTable('stock_movements')) {
                $this->info('Resetting Stock Movements...');
                $stockMovementCount = DB::table('stock_movements')->count();
                DB::table('stock_movements')->truncate();
                $this->line("✓ Deleted {$stockMovementCount} stock movements");
            }

            // Reset Operating Expenses
            if (Schema::hasTable('operating_expenses')) {
                $this->info('Resetting Operating Expenses...');
                $operatingExpenseCount = DB::table('operating_expenses')->count();
                DB::table('operating_expenses')->truncate();
                $this->line("✓ Deleted {$operatingExpenseCount} operating expenses");
            }

            // Delete all products
            if (Schema::hasTable('products')) {
                $this->info('Deleting Products...');
                $productCount = DB::table('products')->count();
                DB::table('products')->truncate();
                $this->line("✓ Deleted {$productCount} products");
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->line('✓ Foreign key checks re-enabled');

            DB::commit();

            $this->info('✅ Data reset completed successfully!');
            $this->newLine();
            $this->info('📊 Summary:');
            $this->line("  • Sales Orders: {$salesOrderCount} deleted");
            $this->line("  • Sales Order Items: {$salesOrderItemCount} deleted");
            $this->line("  • Sales Returns: {$salesReturnCount} deleted");
            $this->line("  • Sales Return Items: {$salesReturnItemCount} deleted");
            $this->line("  • Purchase Orders: {$purchaseOrderCount} deleted");
            $this->line("  • Purchase Order Items: {$purchaseOrderItemCount} deleted");
            $this->line("  • Purchase Returns: {$purchaseReturnCount} deleted");
            $this->line("  • Purchase Return Items: {$purchaseReturnItemCount} deleted");
            $this->line("  • Stock Movements: {$stockMovementCount} deleted");
            $this->line("  • Operating Expenses: {$operatingExpenseCount} deleted");
            $this->line("  • Products: {$productCount} deleted");
            $this->newLine();
            $this->warn('⚠️  This action cannot be undone. Consider taking a backup before running this command in production.');

        } catch (\Exception $e) {
            DB::rollBack();
            // Ensure foreign key checks are re-enabled even on error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $fkException) {
                // Ignore if this fails
            }
            $this->error('❌ Error during data reset: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}