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
    protected $description = 'Reset all sales, purchase, profit/loss, and stock data for testing purposes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('This will permanently delete ALL sales orders, purchase orders, and reset product stock. Are you sure?')) {
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

            // Reset Sales Returns first (since they reference sales orders and items)
            if (Schema::hasTable('sales_return_items')) {
                $this->info('Resetting Sales Return Items...');
                $salesReturnItemCount = DB::table('sales_return_items')->count();
                DB::table('sales_return_items')->truncate();
                $this->line("✓ Deleted {$salesReturnItemCount} sales return items");
            }

            if (Schema::hasTable('sales_returns')) {
                $this->info('Resetting Sales Returns...');
                $salesReturnCount = DB::table('sales_returns')->count();
                DB::table('sales_returns')->truncate();
                $this->line("✓ Deleted {$salesReturnCount} sales returns");
            }

            // Reset Sales Orders and their items
            $this->info('Resetting Sales Orders...');
            $salesOrderItemCount = SalesOrderItem::count();
            SalesOrderItem::truncate();
            SalesOrder::truncate();
            $this->line("✓ Deleted {$salesOrderItemCount} sales order items");
            $this->line('✓ Deleted all sales orders');

            // Reset Purchase Orders
            $this->info('Resetting Purchase Orders...');
            $purchaseOrderCount = PurchaseOrder::count();
            PurchaseOrder::truncate();
            $this->line("✓ Deleted {$purchaseOrderCount} purchase orders");

            // Reset Product stock
            $this->info('Resetting Product Stock...');
            $productCount = Product::count();
            Product::query()->update([
                'quantity_on_hand' => 0,
                'reorder_level' => 0,
                'price' => 0,
                'cost_price' => 0,
                'profit_margin' => 0,
            ]);
            $this->line("✓ Reset stock and pricing for {$productCount} products");

            // Optional: Reset Suppliers (uncomment if needed)
            // $this->info('Resetting Suppliers...');
            // $supplierCount = Supplier::count();
            // Supplier::truncate();
            // $this->line("✓ Deleted {$supplierCount} suppliers");

            DB::commit();

            $this->info('✅ Data reset completed successfully!');
            $this->info('Summary:');
            $this->line('- All sales orders and items deleted');
            $this->line('- All purchase orders deleted');
            $this->line('- All product stock reset to 0');
            $this->line('- All profit/loss data cleared');
            $this->warn('⚠️  This action cannot be undone. Consider taking a backup before running this command in production.');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error during data reset: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}