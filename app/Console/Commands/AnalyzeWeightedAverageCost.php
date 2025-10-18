<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WeightedAverageCostService;
use App\Modules\Products\Models\Product;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;

class AnalyzeWeightedAverageCost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:analyze-wac
                            {--product-id= : Analyze specific product ID}
                            {--show-history : Show detailed purchase history}
                            {--show-analysis : Show comprehensive WAC analysis}
                            {--update-all : Update WAC for all products}
                            {--show-differences : Show products with significant WAC differences}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze Weighted Average Cost (WAC) for products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Weighted Average Cost (WAC) Analysis Tool');
        $this->line('');

        $productId = $this->option('product-id');
        $showHistory = $this->option('show-history');
        $showAnalysis = $this->option('show-analysis');
        $updateAll = $this->option('update-all');
        $showDifferences = $this->option('show-differences');

        if ($updateAll) {
            $this->updateAllWAC();
            return;
        }

        if ($showAnalysis) {
            $this->showWACAnalysis();
            return;
        }

        if ($showDifferences) {
            $this->showWACDifferences();
            return;
        }

        if ($productId) {
            $this->analyzeProduct($productId, $showHistory);
        } else {
            $this->showWACOverview();
        }
    }

    /**
     * Analyze a specific product
     */
    private function analyzeProduct(int $productId, bool $showHistory): void
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->error("❌ Product with ID {$productId} not found.");
            return;
        }

        $this->info("📦 Analyzing Product: {$product->name}");
        $this->line("SKU: " . ($product->sku ?? 'N/A'));
        $this->line('');

        // Current WAC
        $currentWAC = WeightedAverageCostService::calculateWeightedAverageCost($productId);
        $currentStock = $product->getCurrentStock();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Current WAC (COGS)', '$' . number_format($currentWAC, 4)],
                ['Cost Price', '$' . number_format($product->cost_price ?? 0, 2)],
                ['Selling Price', '$' . number_format($product->price ?? 0, 2)],
                ['Current Stock', $currentStock],
                ['Inventory Value @ WAC', '$' . number_format($currentStock * $currentWAC, 2)],
                ['Profit Margin @ WAC', number_format($product->getProfitMarginUsingWAC(), 2) . '%'],
                ['WAC Difference', number_format($product->getWACDifferencePercentage(), 2) . '%'],
            ]
        );

        $this->line('');

        if ($showHistory) {
            $this->showPurchaseHistory($productId);
        }
    }

    /**
     * Show purchase history for a product
     */
    private function showPurchaseHistory(int $productId): void
    {
        $this->info('📋 Purchase History (chronological order):');

        $history = WeightedAverageCostService::getPurchaseHistory($productId);

        if (empty($history)) {
            $this->warn('⚠️  No purchase history found for this product.');
            return;
        }

        $this->table(
            ['Date', 'PO Number', 'Quantity', 'Unit Cost', 'Total Cost', 'Running Qty', 'Running Value', 'WAC'],
            array_map(function ($item) {
                return [
                    $item['order_date'],
                    $item['order_number'],
                    $item['quantity'],
                    '$' . number_format($item['unit_cost'], 2),
                    '$' . number_format($item['total_cost'], 2),
                    $item['running_total_quantity'],
                    '$' . number_format($item['running_total_value'], 2),
                    '$' . number_format($item['current_weighted_average_cost'], 4),
                ];
            }, $history)
        );

        $this->line('');
        $this->info('📊 Final WAC Calculation:');
        $this->line('   Total Value: $' . number_format(end($history)['running_total_value'], 2));
        $this->line('   Total Quantity: ' . end($history)['running_total_quantity']);
        $this->line('   WAC: $' . number_format(end($history)['current_weighted_average_cost'], 4));
    }

    /**
     * Show WAC overview for all products
     */
    private function showWACOverview(): void
    {
        $this->info('📊 Weighted Average Cost Overview');

        $allWAC = WeightedAverageCostService::calculateForAllProducts();
        $totalProducts = count($allWAC);

        if ($totalProducts === 0) {
            $this->warn('⚠️  No products found.');
            return;
        }

        $withHistory = count(array_filter($allWAC, fn($data) => $data['purchase_count'] > 0));
        $withoutHistory = $totalProducts - $withHistory;

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Products', $totalProducts],
                ['With Purchase History', $withHistory],
                ['Without Purchase History', $withoutHistory],
            ]
        );

        $this->line('');

        // Show sample of products with WAC
        $this->info('📦 Sample Products with WAC calculations:');
        $samples = array_slice($allWAC, 0, 10, true);

        $this->table(
            ['Product ID', 'WAC', 'Total Qty Purchased', 'Total Value', 'Purchase Count'],
            array_map(function ($productId, $data) {
                $product = Product::find($productId);
                return [
                    $productId,
                    '$' . number_format($data['weighted_average_cost'], 4),
                    $data['total_quantity_purchased'],
                    '$' . number_format($data['total_value'], 2),
                    $data['purchase_count']
                ];
            }, array_keys($samples), $samples)
        );
    }

    /**
     * Show comprehensive WAC analysis
     */
    private function showWACAnalysis(): void
    {
        $this->info('📊 Comprehensive WAC Analysis');

        $analysis = WeightedAverageCostService::getWACAnalysis();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Products', $analysis['total_products']],
                ['Products with Purchase History', $analysis['products_with_purchase_history']],
                ['Products without Purchase History', $analysis['products_without_purchase_history']],
                ['Average WAC Value', '$' . number_format($analysis['average_wac_value'], 4)],
                ['Total Inventory Value @ WAC', '$' . number_format($analysis['total_inventory_value_at_wac'], 2)],
            ]
        );

        $this->line('');

        // WAC Distribution
        $this->info('📈 WAC Distribution:');
        $this->table(
            ['Cost Range', 'Product Count'],
            [
                ['Low Cost (<$10)', $analysis['wac_distribution']['low_cost']],
                ['Medium Cost ($10-$100)', $analysis['wac_distribution']['medium_cost']],
                ['High Cost (>$100)', $analysis['wac_distribution']['high_cost']],
            ]
        );

        $this->line('');

        // Top cost products
        if (!empty($analysis['top_cost_products'])) {
            $this->info('💰 High-Value Products (WAC > $50):');
            $this->table(
                ['Product', 'WAC', 'Stock', 'Value @ WAC', 'Purchases'],
                array_map(function ($product) {
                    return [
                        substr($product['product_name'], 0, 30),
                        '$' . number_format($product['weighted_average_cost'], 2),
                        $product['current_stock'],
                        '$' . number_format($product['inventory_value_at_wac'], 2),
                        $product['total_purchases']
                    ];
                }, array_slice($analysis['top_cost_products'], 0, 10))
            );
        }
    }

    /**
     * Show products with WAC differences
     */
    private function showWACDifferences(): void
    {
        $this->info('⚠️  Products with Significant WAC Differences (>10%):');

        $analysis = WeightedAverageCostService::getWACAnalysis();

        if (empty($analysis['wac_changes_needed'])) {
            $this->info('✅ No products found with significant WAC differences.');
            return;
        }

        $this->table(
            ['Product', 'Current Cost', 'WAC', 'Difference', '% Diff', 'Recommendation'],
            array_map(function ($product) {
                return [
                    substr($product['product_name'], 0, 30),
                    '$' . number_format($product['current_cost_price'], 2),
                    '$' . number_format($product['weighted_average_cost'], 4),
                    '$' . number_format($product['difference'], 2),
                    number_format($product['percentage_difference'], 1) . '%',
                    $product['recommendation']
                ];
            }, $analysis['wac_changes_needed'])
        );
    }

    /**
     * Update WAC for all products
     */
    private function updateAllWAC(): void
    {
        $this->info('🔄 Updating Weighted Average Cost for all products...');

        $products = Product::all();
        $bar = $this->output->createProgressBar(count($products));

        $bar->start();

        foreach ($products as $product) {
            WeightedAverageCostService::updateWACAfterPurchase($product->id);
            $bar->advance();
        }

        $bar->finish();

        $this->line('');
        $this->info('✅ WAC cache cleared and recalculated for all products.');
    }
}