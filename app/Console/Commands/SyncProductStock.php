<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\Products\Models\Product;
use App\Modules\StockMovement\Models\StockMovement;
use App\Services\WeightedAverageCostService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:sync-stock
                            {--product= : Sync stock for a specific product ID}
                            {--force : Force sync even if no changes detected}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product quantity_on_hand and cost_price (WAC) from stock movements (scheduled to run hourly)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting product stock synchronization...');
        $startTime = now();

        try {
            // If specific product ID is provided, sync only that product
            if ($productId = $this->option('product')) {
                $this->syncSingleProduct($productId);
                return Command::SUCCESS;
            }

            // Sync all products
            $this->syncAllProducts();

            $duration = now()->diffInSeconds($startTime);
            $this->info("Stock synchronization completed in {$duration} seconds.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during stock synchronization: ' . $e->getMessage());
            Log::error('Product stock sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Sync stock for a single product
     */
    protected function syncSingleProduct(int $productId): void
    {
        $product = Product::find($productId);

        if (!$product) {
            $this->error("Product with ID {$productId} not found.");
            return;
        }

        $this->info("Syncing stock and cost price for product: {$product->name} (ID: {$productId})");

        // Calculate stock from movements
        $calculatedStock = $this->calculateStockFromMovements($productId);
        $oldStock = $product->quantity_on_hand;

        // Calculate WAC (Weighted Average Cost)
        $calculatedWAC = WeightedAverageCostService::calculateWeightedAverageCost($productId);
        $oldCostPrice = $product->cost_price;

        $stockChanged = $oldStock !== $calculatedStock;
        $costChanged = abs($oldCostPrice - $calculatedWAC) > 0.01; // Allow 1 cent difference

        if ($stockChanged || $costChanged || $this->option('force')) {
            if ($stockChanged) {
                $product->quantity_on_hand = $calculatedStock;
            }
            if ($costChanged) {
                $product->cost_price = round($calculatedWAC, 2);
            }

            $product->save();

            $this->info("✓ Updated: {$product->name}");
            if ($stockChanged) {
                $this->line("  Stock: {$oldStock} → {$calculatedStock}");
            }
            if ($costChanged) {
                $this->line("  Cost Price (WAC): $" . number_format($oldCostPrice, 2) . " → $" . number_format($calculatedWAC, 2));
            }

            Log::info('Product synced', [
                'product_id' => $productId,
                'product_name' => $product->name,
                'old_stock' => $oldStock,
                'new_stock' => $calculatedStock,
                'stock_difference' => $calculatedStock - $oldStock,
                'old_cost_price' => $oldCostPrice,
                'new_cost_price' => $calculatedWAC,
                'cost_difference' => $calculatedWAC - $oldCostPrice
            ]);
        } else {
            $this->line("✓ No change: {$product->name} (stock: {$calculatedStock}, cost: $" . number_format($calculatedWAC, 2) . ")");
        }
    }

    /**
     * Sync stock for all products
     */
    protected function syncAllProducts(): void
    {
        // Get all products with their calculated stock from movements
        $stockData = DB::table('stock_movements')
            ->select(
                'product_id',
                DB::raw("SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out")
            )
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // Get WAC for all products
        $this->line("Calculating Weighted Average Cost for all products...");
        $wacData = WeightedAverageCostService::calculateForAllProducts();

        $products = Product::all();
        $totalProducts = $products->count();
        $updatedCount = 0;
        $unchangedCount = 0;
        $stockUpdates = 0;
        $costUpdates = 0;

        $this->info("Processing {$totalProducts} products...");
        $progressBar = $this->output->createProgressBar($totalProducts);

        foreach ($products as $product) {
            // Calculate stock from movements
            $stockInfo = $stockData->get($product->id);
            $calculatedStock = $stockInfo
                ? ($stockInfo->total_in - $stockInfo->total_out)
                : 0;

            // Get WAC
            $wacInfo = $wacData[$product->id] ?? null;
            $calculatedWAC = $wacInfo ? $wacInfo['weighted_average_cost'] : ($product->cost_price ?? 0);

            $oldStock = $product->quantity_on_hand;
            $oldCostPrice = $product->cost_price;

            $stockChanged = $oldStock !== $calculatedStock;
            $costChanged = abs($oldCostPrice - $calculatedWAC) > 0.01; // Allow 1 cent difference

            // Update if there's a difference
            if ($stockChanged || $costChanged || $this->option('force')) {
                if ($stockChanged) {
                    $product->quantity_on_hand = $calculatedStock;
                    $stockUpdates++;
                }
                if ($costChanged) {
                    $product->cost_price = round($calculatedWAC, 2);
                    $costUpdates++;
                }

                $product->save();
                $updatedCount++;

                Log::info('Product synced', [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_stock' => $oldStock,
                    'new_stock' => $calculatedStock,
                    'stock_difference' => $calculatedStock - $oldStock,
                    'old_cost_price' => $oldCostPrice,
                    'new_cost_price' => $calculatedWAC,
                    'cost_difference' => $calculatedWAC - $oldCostPrice
                ]);
            } else {
                $unchangedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Summary:");
        $this->line("  Total products: {$totalProducts}");
        $this->line("  Products updated: {$updatedCount}");
        $this->line("  - Stock updated: {$stockUpdates}");
        $this->line("  - Cost price (WAC) updated: {$costUpdates}");
        $this->line("  Products unchanged: {$unchangedCount}");

        Log::info('Product sync completed', [
            'total_products' => $totalProducts,
            'updated' => $updatedCount,
            'stock_updates' => $stockUpdates,
            'cost_updates' => $costUpdates,
            'unchanged' => $unchangedCount
        ]);
    }

    /**
     * Calculate stock from movements for a specific product
     */
    protected function calculateStockFromMovements(int $productId): int
    {
        $result = DB::table('stock_movements')
            ->where('product_id', $productId)
            ->select(
                DB::raw("SUM(CASE WHEN movement_type = 'in' THEN quantity ELSE 0 END) as total_in"),
                DB::raw("SUM(CASE WHEN movement_type = 'out' THEN quantity ELSE 0 END) as total_out")
            )
            ->first();

        return ($result->total_in ?? 0) - ($result->total_out ?? 0);
    }
}
