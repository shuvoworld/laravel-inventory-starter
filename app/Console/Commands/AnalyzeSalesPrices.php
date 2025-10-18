<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SalesPriceAnalysisService;
use App\Modules\Products\Models\Product;

class AnalyzeSalesPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:analyze-sales-prices
                            {--product-id= : Analyze specific product ID}
                            {--show-history : Show detailed sales history}
                            {--show-trends : Show price trends over time}
                            {--period=30 : Number of days for analysis (default: 30)}
                            {--show-compliance : Show pricing compliance analysis}
                            {--show-recommendations : Show pricing recommendations}
                            {--overall-analysis : Show overall pricing analysis for all products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze actual sales prices and pricing effectiveness';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('💰 Sales Price Analysis Tool');
        $this->line('');

        $productId = $this->option('product-id');
        $showHistory = $this->option('show-history');
        $showTrends = $this->option('show-trends');
        $showCompliance = $this->option('show-compliance');
        $showRecommendations = $this->option('show-recommendations');
        $overallAnalysis = $this->option('overall-analysis');
        $period = $this->option('period');

        if ($overallAnalysis) {
            $this->showOverallAnalysis();
            return;
        }

        if ($showCompliance) {
            $this->showPricingCompliance();
            return;
        }

        if ($productId) {
            $this->analyzeProduct($productId, $showHistory, $showTrends, $showRecommendations, $period);
        } else {
            $this->showSalesPriceOverview($period);
        }
    }

    /**
     * Analyze a specific product
     */
    private function analyzeProduct(int $productId, bool $showHistory, bool $showTrends, bool $showRecommendations, int $period): void
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->error("❌ Product with ID {$productId} not found.");
            return;
        }

        $this->info("💰 Analyzing Sales Prices for: {$product->name}");
        $this->line("SKU: " . ($product->sku ?? 'N/A'));
        $this->line('');

        // Calculate date range
        $endDate = now();
        $startDate = now()->subDays($period);

        // Get sales statistics
        $stats = SalesPriceAnalysisService::getProductSalesPriceStats($productId, $startDate, $endDate);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Base Price', '$' . number_format($product->price, 2)],
                ['Average Actual Price', '$' . number_format($stats['average_price_per_unit'], 2)],
                ['Minimum Price Sold', '$' . number_format($stats['minimum_unit_price'], 2)],
                ['Maximum Price Sold', '$' . number_format($stats['maximum_unit_price'], 2)],
                ['Price Range', '$' . number_format($stats['price_variance'], 2)],
                ['Total Transactions', $stats['total_transactions']],
                ['Total Quantity Sold', $stats['total_quantity_sold']],
                ['Total Revenue', '$' . number_format($stats['total_revenue'], 2)],
                ['Total Discounts', '$' . number_format($stats['total_discounts'], 2)],
                ['Average Discount/Unit', '$' . number_format($stats['average_discount_per_unit'], 2)],
            ]
        );

        $this->line('');

        // Pricing compliance
        $compliance = $product->getPricingCompliance();
        $this->table(
            ['Pricing Compliance', 'Details'],
            [
                ['Status', $compliance['status']],
                ['Base Price', '$' . number_format($compliance['base_price'], 2)],
                ['Average Actual Price', '$' . number_format($compliance['average_actual_price'], 2)],
                ['Price Difference', '$' . number_format($compliance['price_difference'], 2)],
                ['Difference %', number_format($compliance['difference_percentage'], 1) . '%'],
            ]
        );

        $this->info('💡 Recommendation: ' . $compliance['recommendation']);
        $this->line('');

        // Profit margin analysis
        $actualMargin = $product->getActualProfitMargin();
        $this->table(
            ['Profit Analysis', 'Details'],
            [
                ['WAC (COGS)', '$' . number_format($product->getWeightedAverageCost(), 2)],
                ['Actual Selling Price', '$' . number_format($stats['average_price_per_unit'], 2)],
                ['Actual Profit Margin', number_format($actualMargin, 2) . '%'],
                ['Theoretical Margin (Base)', number_format($product->getProfitMargin(), 2) . '%'],
            ]
        );

        $this->line('');

        if ($showHistory) {
            $this->showSalesHistory($productId, $startDate, $endDate);
        }

        if ($showTrends) {
            $this->showPriceTrends($productId, $startDate, $endDate);
        }

        if ($showRecommendations) {
            $this->showPricingRecommendations($productId);
        }
    }

    /**
     * Show sales history for a product
     */
    private function showSalesHistory(int $productId, $startDate, $endDate): void
    {
        $this->info('📋 Recent Sales History:');

        $history = SalesPriceAnalysisService::getProductSalesHistory($productId, $startDate, $endDate);

        if ($history->isEmpty()) {
            $this->warn('⚠️  No sales history found for this product in the specified period.');
            return;
        }

        $this->table(
            ['Date', 'Order #', 'Quantity', 'Unit Price', 'Total Price', 'Discount', 'Final Price'],
            $history->map(function ($sale) {
                return [
                    $sale->order_date,
                    $sale->order_number,
                    $sale->quantity,
                    '$' . number_format($sale->unit_price, 2),
                    '$' . number_format($sale->unit_price * $sale->quantity, 2),
                    '$' . number_format($sale->discount_amount, 2),
                    '$' . number_format($sale->final_price, 2)
                ];
            })->take(20)
        );

        if ($history->count() > 20) {
            $this->line('... (showing first 20 transactions)');
        }
    }

    /**
     * Show price trends for a product
     */
    private function showPriceTrends(int $productId, $startDate, $endDate): void
    {
        $this->info('📈 Price Trends:');

        $trends = SalesPriceAnalysisService::getProductSalesPriceTrends(
            $productId,
            $startDate,
            $endDate,
            'day'
        );

        if (empty($trends)) {
            $this->warn('⚠️  No price trend data available.');
            return;
        }

        $this->table(
            ['Date', 'Transactions', 'Quantity', 'Avg Price', 'Min Price', 'Max Price', 'Price Range'],
            array_map(function ($trend) {
                return [
                    $trend['period'],
                    $trend['transaction_count'],
                    $trend['quantity_sold'],
                    '$' . number_format($trend['average_price'], 2),
                    '$' . number_format($trend['minimum_price'], 2),
                    '$' . number_format($trend['maximum_price'], 2),
                    '$' . number_format($trend['price_range'], 2)
                ];
            }, array_slice($trends, 0, 15))
        );

        if (count($trends) > 15) {
            $this->line('... (showing first 15 days)');
        }
    }

    /**
     * Show pricing recommendations
     */
    private function showPricingRecommendations(int $productId): void
    {
        $this->info('🎯 Pricing Recommendations:');

        $recommendations = SalesPriceAnalysisService::getPricingRecommendations($productId);

        if (empty($recommendations)) {
            $this->info('✅ No specific pricing recommendations. Current pricing strategy appears optimal.');
            return;
        }

        foreach ($recommendations as $rec) {
            $priorityIcon = $rec['priority'] === 'high' ? '🔴' : ($rec['priority'] === 'medium' ? '🟡' : '🟢');
            $this->line("{$priorityIcon} {$rec['message']}");
            $this->line("   💡 Action: {$rec['suggested_action']}");
            $this->line('');
        }
    }

    /**
     * Show overall sales price overview
     */
    private function showSalesPriceOverview(int $period): void
    {
        $this->info('📊 Overall Sales Price Overview (Last ' . $period . ' days)');

        $analysis = SalesPriceAnalysisService::getOverallPricingAnalysis();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Products', $analysis['total_products']],
                ['Products with Sales', $analysis['products_with_sales']],
                ['Products without Sales', $analysis['products_without_sales']],
                ['Total Revenue', '$' . number_format($analysis['total_revenue'], 2)],
                ['Total Transactions', $analysis['total_transactions']],
                ['Overall Average Price', '$' . number_format($analysis['overall_average_price'], 2)],
                ['Total Discounts Given', '$' . number_format($analysis['discount_analysis']['total_discounts_given'], 2)],
                ['Average Discount Rate', number_format($analysis['discount_analysis']['average_discount_rate'], 1) . '%'],
            ]
        );

        $this->line('');

        // Pricing consistency
        $this->info('📊 Pricing Consistency:');
        $this->table(
            ['Consistency Level', 'Product Count'],
            [
                ['Consistent (<5% variance)', $analysis['pricing_consistency']['consistent_pricing']],
                ['Variable (5-20% variance)', $analysis['pricing_consistency']['variable_pricing']],
                ['High Variance (>20% variance)', $analysis['pricing_consistency']['high_variance']],
            ]
        );

        $this->line('');

        // Top performing products
        if (!empty($analysis['top_performing_products'])) {
            $this->info('🏆 Top Performing Products by Revenue:');
            $this->table(
                ['Product', 'Revenue', 'Transactions', 'Avg Price'],
                array_map(function ($product) {
                    return [
                        substr($product['product_name'], 0, 30),
                        '$' . number_format($product['revenue'], 2),
                        $product['transactions'],
                        '$' . number_format($product['average_price'], 2)
                    ];
                }, array_slice($analysis['top_performing_products'], 0, 10))
            );
        }
    }

    /**
     * Show pricing compliance analysis
     */
    private function showPricingCompliance(): void
    {
        $this->info('📋 Pricing Compliance Analysis (Base Price vs Actual Selling Price)');

        $complianceData = SalesPriceAnalysisService::getPriceComplianceAnalysis();

        if (empty($complianceData)) {
            $this->info('✅ No products with sales history found for compliance analysis.');
            return;
        }

        $this->table(
            ['Product', 'Base Price', 'Actual Avg Price', 'Difference', '% Diff', 'Status', 'Transactions'],
            array_map(function ($product) {
                return [
                    substr($product['product_name'], 0, 25),
                    '$' . number_format($product['base_price'], 2),
                    '$' . number_format($product['average_actual_price'], 2),
                    '$' . number_format($product['price_difference'], 2),
                    number_format($product['price_difference_percentage'], 1) . '%',
                    $product['compliance_status'],
                    $product['total_transactions']
                ];
            }, array_slice($complianceData, 0, 20))
        );

        if (count($complianceData) > 20) {
            $this->line('... (showing first 20 products)');
        }

        $this->line('');

        // Summary statistics
        $excellentCount = count(array_filter($complianceData, fn($p) => $p['compliance_status'] === 'Excellent'));
        $poorCount = count(array_filter($complianceData, fn($p) => $p['compliance_status'] === 'Poor'));

        $this->info('📊 Compliance Summary:');
        $this->line("   Excellent Compliance (<5% difference): {$excellentCount} products");
        $this->line("   Poor Compliance (>20% difference): {$poorCount} products");
        $this->line("   Total Products Analyzed: " . count($complianceData));
    }

    /**
     * Show overall pricing analysis
     */
    private function showOverallAnalysis(): void
    {
        $this->info('📊 Comprehensive Pricing Analysis');

        $analysis = SalesPriceAnalysisService::getOverallPricingAnalysis();

        // Basic metrics
        $this->table(
            ['Overall Metrics', 'Value'],
            [
                ['Total Products', $analysis['total_products']],
                ['Products with Sales', $analysis['products_with_sales']],
                ['Products without Sales', $analysis['products_without_sales']],
                ['Total Revenue', '$' . number_format($analysis['total_revenue'], 2)],
                ['Overall Average Price', '$' . number_format($analysis['overall_average_price'], 2)],
            ]
        );

        $this->line('');

        // Pricing consistency analysis
        $this->info('📊 Pricing Consistency Analysis:');
        $this->table(
            ['Consistency Level', 'Product Count', 'Percentage'],
            [
                ['Consistent Pricing', $analysis['pricing_consistency']['consistent_pricing'],
                 number_format(($analysis['pricing_consistency']['consistent_pricing'] / max($analysis['products_with_sales'], 1)) * 100, 1) . '%'],
                ['Variable Pricing', $analysis['pricing_consistency']['variable_pricing'],
                 number_format(($analysis['pricing_consistency']['variable_pricing'] / max($analysis['products_with_sales'], 1)) * 100, 1) . '%'],
                ['High Variance', $analysis['pricing_consistency']['high_variance'],
                 number_format(($analysis['pricing_consistency']['high_variance'] / max($analysis['products_with_sales'], 1)) * 100, 1) . '%'],
            ]
        );

        $this->line('');

        // Discount analysis
        $this->info('💰 Discount Analysis:');
        $this->table(
            ['Discount Metrics', 'Value'],
            [
                ['Total Discounts Given', '$' . number_format($analysis['discount_analysis']['total_discounts_given'], 2)],
                ['Average Discount Rate', number_format($analysis['discount_analysis']['average_discount_rate'], 1) . '%'],
                ['Products with Discounts', $analysis['discount_analysis']['products_with_discounts']],
            ]
        );

        $this->line('');

        // Insights and recommendations
        $this->info('💡 Key Insights:');

        if ($analysis['pricing_consistency']['high_variance'] > 0) {
            $this->line('⚠️  ' . $analysis['pricing_consistency']['high_variance'] . ' products have high price variance (>20%). Consider standardizing pricing.');
        }

        if ($analysis['discount_analysis']['average_discount_rate'] > 15) {
            $this->line('💸 Average discount rate is ' . number_format($analysis['discount_analysis']['average_discount_rate'], 1) . '%. Review discount policies.');
        }

        if ($analysis['products_without_sales'] > 0) {
            $this->line('📦 ' . $analysis['products_without_sales'] . ' products have no sales. Consider marketing initiatives or price adjustments.');
        }

        if (!empty($analysis['top_performing_products'])) {
            $this->line('🏆 Top performing products generate significant revenue. Ensure inventory availability.');
        }
    }
}