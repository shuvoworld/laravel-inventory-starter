<?php

namespace App\Services;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class SalesPriceAnalysisService
{
    /**
     * Get actual sales price for a specific product in a specific sale
     */
    public static function getSalesPriceForTransaction(int $salesOrderId, int $productId): float
    {
        $item = SalesOrderItem::where('sales_order_id', $salesOrderId)
            ->where('product_id', $productId)
            ->first(['quantity', 'final_price']);

        if ($item && $item->quantity > 0) {
            return $item->final_price / $item->quantity;
        }

        return 0;
    }

    /**
     * Get all sales prices for a product across all transactions
     */
    public static function getProductSalesHistory(int $productId, ?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_order_items.product_id', $productId)
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->orderBy('sales_orders.order_date', 'desc');

        if ($startDate) {
            $query->where('sales_orders.order_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('sales_orders.order_date', '<=', $endDate);
        }

        return $query->get([
            'sales_orders.id as sales_order_id',
            'sales_orders.order_number',
            'sales_orders.order_date',
            'sales_order_items.quantity',
            'sales_order_items.unit_price',
            'sales_order_items.final_price',
            'sales_order_items.discount_amount',
            'sales_order_items.discount_rate',
            'sales_orders.customer_id'
        ]);
    }

    /**
     * Get sales price statistics for a product
     */
    public static function getProductSalesPriceStats(int $productId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_order_items.product_id', $productId)
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'shipped', 'delivered']);

        if ($startDate) {
            $query->where('sales_orders.order_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('sales_orders.order_date', '<=', $endDate);
        }

        $sales = $query->selectRaw("
            COUNT(*) as total_transactions,
            SUM(sales_order_items.quantity) as total_quantity_sold,
            AVG(sales_order_items.final_price / sales_order_items.quantity) as average_unit_price,
            MIN(sales_order_items.final_price / sales_order_items.quantity) as minimum_unit_price,
            MAX(sales_order_items.final_price / sales_order_items.quantity) as maximum_unit_price,
            AVG(sales_order_items.final_price / sales_order_items.quantity) as average_price_per_unit,
            SUM(sales_order_items.final_price) as total_revenue,
            SUM(sales_order_items.discount_amount) as total_discounts
        ")->first();

        $basePrice = Product::find($productId)?->price ?? 0;

        return [
            'product_id' => $productId,
            'total_transactions' => $sales->total_transactions ?? 0,
            'total_quantity_sold' => $sales->total_quantity_sold ?? 0,
            'average_unit_price' => $sales->average_unit_price ?? 0,
            'minimum_unit_price' => $sales->minimum_unit_price ?? 0,
            'maximum_unit_price' => $sales->maximum_unit_price ?? 0,
            'average_price_per_unit' => $sales->average_price_per_unit ?? 0,
            'total_revenue' => $sales->total_revenue ?? 0,
            'total_discounts' => $sales->total_discounts ?? 0,
            'base_price' => $basePrice,
            'price_variance' => $sales->average_unit_price ? ($sales->maximum_unit_price - $sales->minimum_unit_price) : 0,
            'average_discount_per_unit' => $sales->total_quantity_sold ? ($sales->total_discounts / $sales->total_quantity_sold) : 0
        ];
    }

    /**
     * Get sales price trends over time for a product
     */
    public static function getProductSalesPriceTrends(int $productId, Carbon $startDate, Carbon $endDate, string $period = 'month'): array
    {
        $dateFormat = $period === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $query = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_order_items.product_id', $productId)
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
            ->selectRaw("
                DATE_FORMAT(sales_orders.order_date, '{$dateFormat}') as period,
                COUNT(*) as transaction_count,
                SUM(sales_order_items.quantity) as quantity_sold,
                AVG(sales_order_items.final_price / sales_order_items.quantity) as average_price,
                MIN(sales_order_items.final_price / sales_order_items.quantity) as minimum_price,
                MAX(sales_order_items.final_price / sales_order_items.quantity) as maximum_price,
                SUM(sales_order_items.final_price) as revenue,
                SUM(sales_order_items.discount_amount) as discounts
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $query->map(function ($item) {
            return [
                'period' => $item->period,
                'transaction_count' => $item->transaction_count,
                'quantity_sold' => $item->quantity_sold,
                'average_price' => round($item->average_price, 2),
                'minimum_price' => $item->minimum_price,
                'maximum_price' => $item->maximum_price,
                'price_range' => $item->maximum_price - $item->minimum_price,
                'revenue' => $item->revenue,
                'discounts' => $item->discounts,
                'average_discount_per_unit' => $item->quantity_sold ? ($item->discounts / $item->quantity_sold) : 0
            ];
        })->toArray();
    }

    /**
     * Analyze pricing effectiveness for all products
     */
    public static function getOverallPricingAnalysis(): array
    {
        $analysis = [
            'total_products' => 0,
            'products_with_sales' => 0,
            'products_without_sales' => 0,
            'total_revenue' => 0,
            'total_transactions' => 0,
            'overall_average_price' => 0,
            'pricing_consistency' => [
                'consistent_pricing' => 0,
                'variable_pricing' => 0,
                'high_variance' => 0
            ],
            'discount_analysis' => [
                'total_discounts_given' => 0,
                'average_discount_rate' => 0,
                'products_with_discounts' => 0
            ],
            'top_performing_products' => [],
            'underperforming_products' => [],
            'pricing_recommendations' => []
        ];

        // Get all products with their sales stats
        $query = Product::query();

        // Only filter by store_id if we're in a web context (not console)
        if (app()->runningInConsole()) {
            // In console, get all products
        } else {
            $query->where('store_id', auth()->user()->store_id);
        }

        $products = $query->get();
        $analysis['total_products'] = $products->count();

        $productStats = [];
        foreach ($products as $product) {
            $stats = self::getProductSalesPriceStats($product->id);
            $productStats[$product->id] = [
                'product' => $product,
                'stats' => $stats
            ];

            if ($stats['total_transactions'] > 0) {
                $analysis['products_with_sales']++;
                $analysis['total_revenue'] += $stats['total_revenue'];
                $analysis['total_transactions'] += $stats['total_transactions'];

                // Analyze pricing consistency
                $priceVariancePercentage = $stats['average_unit_price'] > 0
                    ? ($stats['price_variance'] / $stats['average_unit_price']) * 100
                    : 0;

                if ($priceVariancePercentage < 5) {
                    $analysis['pricing_consistency']['consistent_pricing']++;
                } elseif ($priceVariancePercentage < 20) {
                    $analysis['pricing_consistency']['variable_pricing']++;
                } else {
                    $analysis['pricing_consistency']['high_variance']++;
                }

                // Discount analysis
                if ($stats['total_discounts'] > 0) {
                    $analysis['discount_analysis']['total_discounts_given'] += $stats['total_discounts'];
                    $analysis['discount_analysis']['products_with_discounts']++;
                }

                // Top performers (high revenue)
                if ($stats['total_revenue'] > 1000) {
                    $analysis['top_performing_products'][] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'revenue' => $stats['total_revenue'],
                        'transactions' => $stats['total_transactions'],
                        'average_price' => $stats['average_unit_price']
                    ];
                }

                // Underperformers (low or no sales)
                if ($stats['total_revenue'] < 100 && $product->price > 0) {
                    $analysis['underperforming_products'][] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'base_price' => $stats['base_price'],
                        'actual_average_price' => $stats['average_unit_price'],
                        'revenue' => $stats['total_revenue'],
                        'transactions' => $stats['total_transactions']
                    ];
                }
            } else {
                $analysis['products_without_sales']++;
            }
        }

        $analysis['products_without_sales'] = $analysis['total_products'] - $analysis['products_with_sales'];

        // Calculate overall averages
        $analysis['overall_average_price'] = $analysis['total_transactions'] > 0
            ? array_sum(array_column(array_column($productStats, 'stats'), 'average_unit_price')) / count(array_filter($productStats, fn($item) => $item['stats']['total_transactions'] > 0))
            : 0;

        $analysis['discount_analysis']['average_discount_rate'] = $analysis['total_revenue'] > 0
            ? ($analysis['discount_analysis']['total_discounts_given'] / $analysis['total_revenue']) * 100
            : 0;

        // Sort products by revenue
        usort($analysis['top_performing_products'], fn($a, $b) => $b['revenue'] <=> $a['revenue']);
        usort($analysis['underperforming_products'], fn($a, $b) => $a['revenue'] <=> $b['revenue']);

        return $analysis;
    }

    /**
     * Compare actual sales prices to base prices
     */
    public static function getPriceComplianceAnalysis(): array
    {
        $query = Product::query();

        // Only filter by store_id if we're in a web context (not console)
        if (app()->runningInConsole()) {
            // In console, get all products
        } else {
            $query->where('store_id', auth()->user()->store_id);
        }

        $products = $query->get();
        $complianceData = [];

        foreach ($products as $product) {
            $stats = self::getProductSalesPriceStats($product->id);

            if ($stats['total_transactions'] > 0) {
                $basePrice = $product->price;
                $averageActualPrice = $stats['average_price_per_unit'];
                $priceDifference = $averageActualPrice - $basePrice;
                $priceDifferencePercentage = $basePrice > 0 ? ($priceDifference / $basePrice) * 100 : 0;

                $complianceData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'base_price' => $basePrice,
                    'average_actual_price' => $averageActualPrice,
                    'price_difference' => $priceDifference,
                    'price_difference_percentage' => $priceDifferencePercentage,
                    'total_transactions' => $stats['total_transactions'],
                    'total_quantity_sold' => $stats['total_quantity_sold'],
                    'minimum_price_sold' => $stats['minimum_unit_price'],
                    'maximum_price_sold' => $stats['maximum_unit_price'],
                    'compliance_status' => self::getComplianceStatus($priceDifferencePercentage)
                ];
            }
        }

        // Sort by compliance issues
        usort($complianceData, fn($a, $b) => abs($b['price_difference_percentage']) <=> abs($a['price_difference_percentage']));

        return $complianceData;
    }

    /**
     * Get pricing recommendations based on sales data
     */
    public static function getPricingRecommendations(int $productId): array
    {
        $product = Product::find($productId);
        if (!$product) {
            return [];
        }

        $stats = self::getProductSalesPriceStats($productId);
        $recommendations = [];

        // No sales data
        if ($stats['total_transactions'] === 0) {
            $recommendations[] = [
                'type' => 'no_sales_data',
                'priority' => 'high',
                'message' => 'No sales history available. Consider running promotions to gather pricing data.',
                'suggested_action' => 'Create promotional pricing or bundle offers to test market response.'
            ];
            return $recommendations;
        }

        // Price variance analysis
        $variancePercentage = $stats['average_unit_price'] > 0
            ? ($stats['price_variance'] / $stats['average_unit_price']) * 100
            : 0;

        if ($variancePercentage > 30) {
            $recommendations[] = [
                'type' => 'high_variance',
                'priority' => 'medium',
                'message' => 'High price variance detected (' . round($variancePercentage, 1) . '%). Consider standardizing pricing.',
                'suggested_action' => 'Review discount policies and establish consistent pricing guidelines.'
            ];
        }

        // Base price vs actual price comparison
        $basePrice = $product->price;
        $averageActualPrice = $stats['average_price_per_unit'];
        $priceDifferencePercentage = $basePrice > 0 ? (($averageActualPrice - $basePrice) / $basePrice) * 100 : 0;

        if (abs($priceDifferencePercentage) > 15) {
            $recommendations[] = [
                'type' => 'price_mismatch',
                'priority' => 'high',
                'message' => 'Significant difference between base price and actual selling price (' . round($priceDifferencePercentage, 1) . '%).',
                'suggested_action' => $priceDifferencePercentage > 0
                    ? 'Consider increasing base price to capture more margin.'
                    : 'Review discount policies and competitive positioning.'
            ];
        }

        // Low transaction volume
        if ($stats['total_transactions'] < 5 && $stats['total_quantity_sold'] < 10) {
            $recommendations[] = [
                'type' => 'low_volume',
                'priority' => 'medium',
                'message' => 'Low sales volume. Consider marketing initiatives or price adjustments.',
                'suggested_action' => 'Analyze market demand and competitor pricing to optimize positioning.'
            ];
        }

        // High discount rate
        $averageDiscountRate = $stats['total_revenue'] > 0
            ? ($stats['total_discounts'] / $stats['total_revenue']) * 100
            : 0;

        if ($averageDiscountRate > 15) {
            $recommendations[] = [
                'type' => 'high_discounts',
                'priority' => 'medium',
                'message' => 'High average discount rate (' . round($averageDiscountRate, 1) . '%). Review discount strategy.',
                'suggested_action' => 'Evaluate if discounts are driving sales or eroding margins unnecessarily.'
            ];
        }

        return $recommendations;
    }

    /**
     * Helper method to determine compliance status
     */
    private static function getComplianceStatus(float $percentageDifference): string
    {
        $absDifference = abs($percentageDifference);

        if ($absDifference < 5) {
            return 'Excellent';
        } elseif ($absDifference < 10) {
            return 'Good';
        } elseif ($absDifference < 20) {
            return 'Fair';
        } else {
            return 'Poor';
        }
    }

    /**
     * Clear sales price cache
     */
    public static function clearCache(): void
    {
        // Clear any cached sales price data if implemented in future
    }
}