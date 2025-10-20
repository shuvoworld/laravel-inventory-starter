<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Products\Models\Product;
use App\Modules\StockMovement\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SyncProductAverageCost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:sync-average-cost {--product= : Specific product ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product cost_price with Average Cost calculated from stock movements (Total Cost / Total Quantity)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Average Cost synchronization...');

        $productId = $this->option('product');

        if ($productId) {
            // Sync specific product
            $product = Product::find($productId);
            if (!$product) {
                $this->error("Product with ID {$productId} not found.");
                return 1;
            }

            $this->syncProductAverageCost($product);
            $this->info("Synced product: {$product->name} (ID: {$product->id})");
        } else {
            // Sync all products
            $products = Product::all();
            $bar = $this->output->createProgressBar($products->count());
            $bar->start();

            $updated = 0;
            foreach ($products as $product) {
                if ($this->syncProductAverageCost($product)) {
                    $updated++;
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Successfully synced {$updated} products.");
        }

        return 0;
    }

    /**
     * Calculate and sync average cost for a product
     */
    private function syncProductAverageCost(Product $product): bool
    {
        // Calculate average cost directly from purchase order items
        $purchaseOrderItems = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_order_items.product_id', $product->id)
            ->whereIn('purchase_orders.status', ['confirmed', 'processing', 'received'])
            ->select(
                DB::raw('SUM(purchase_order_items.quantity) as total_quantity'),
                DB::raw('SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_cost')
            )
            ->first();

        if (!$purchaseOrderItems || $purchaseOrderItems->total_quantity == 0) {
            // No purchase history, keep existing cost_price (don't change it)
            // This preserves the cost even if current stock is 0
            return false;
        }

        // Calculate Average Cost = Total Cost / Total Quantity
        $averageCost = $purchaseOrderItems->total_cost / $purchaseOrderItems->total_quantity;

        // Update product cost_price only if there's purchase history
        $product->update(['cost_price' => round($averageCost, 2)]);

        return true;
    }
}
