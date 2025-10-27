<?php

namespace App\Services;

use App\Modules\Products\Models\ProductVariant;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VariantAnalyticsService
{
    /**
     * Get comprehensive variant analytics dashboard data
     */
    public static function getDashboardData(): array
    {
        $cacheKey = 'variant_analytics_dashboard';

        return Cache::remember($cacheKey, now()->addMinutes(15), function() {
            return [
                'overview' => self::getOverviewStats(),
                'performance' => self::getPerformanceMetrics(),
                'inventory' => self::getInventoryAnalytics(),
                'trends' => self::getTrendsData(),
                'alerts' => self::getAlerts(),
                'top_performers' => self::getTopPerformers(),
            ];
        });
    }

    /**
     * Get overview statistics
     */
    private static function getOverviewStats(): array
    {
        return [
            'total_variants' => ProductVariant::count(),
            'active_variants' => ProductVariant::where('is_active', true)->count(),
            'total_variants_with_stock' => ProductVariant::where('quantity_on_hand', '>', 0)->count(),
            'total_variants_out_of_stock' => ProductVariant::where('quantity_on_hand', '<=', 0)->count(),
            'low_stock_variants' => ProductVariant::whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->where('quantity_on_hand', '>', 0)
                ->count(),
            'total_stock_value' => self::calculateTotalStockValue(),
            'average_variant_price' => ProductVariant::where('is_active', true)->avg('target_price') ?? 0,
            'total_products_with_variants' => \App\Modules\Products\Models\Product::where('has_variants', true)->count(),
        ];
    }

    /**
     * Get performance metrics
     */
    private static function getPerformanceMetrics(): array
    {
        $last30Days = now()->subDays(30);

        $salesData = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.order_date', '>=', $last30Days)
            ->whereNotNull('sales_order_items.variant_id')
            ->select([
                DB::raw('COUNT(DISTINCT sales_order_items.variant_id) as variants_sold'),
                DB::raw('SUM(sales_order_items.quantity) as total_quantity_sold'),
                DB::raw('SUM(sales_order_items.final_price) as total_revenue'),
                DB::raw('SUM(sales_order_items.profit_amount) as total_profit'),
                DB::raw('AVG(sales_order_items.final_price / sales_order_items.quantity) as avg_unit_price'),
            ])
            ->first();

        $purchaseData = PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_orders.order_date', '>=', $last30Days)
            ->whereNotNull('purchase_order_items.variant_id')
            ->select([
                DB::raw('COUNT(DISTINCT purchase_order_items.variant_id) as variants_purchased'),
                DB::raw('SUM(purchase_order_items.quantity) as total_quantity_purchased'),
                DB::raw('SUM(purchase_order_items.total_price) as total_purchase_cost'),
                DB::raw('AVG(purchase_order_items.unit_price) as avg_unit_cost'),
            ])
            ->first();

        $inventoryTurnover = self::calculateInventoryTurnover();

        return [
            'sales' => [
                'variants_sold' => $salesData->variants_sold ?? 0,
                'total_quantity_sold' => $salesData->total_quantity_sold ?? 0,
                'total_revenue' => $salesData->total_revenue ?? 0,
                'total_profit' => $salesData->total_profit ?? 0,
                'avg_unit_price' => $salesData->avg_unit_price ?? 0,
                'profit_margin' => $salesData->total_revenue > 0 ? (($salesData->total_profit / $salesData->total_revenue) * 100) : 0,
            ],
            'purchases' => [
                'variants_purchased' => $purchaseData->variants_purchased ?? 0,
                'total_quantity_purchased' => $purchaseData->total_quantity_purchased ?? 0,
                'total_purchase_cost' => $purchaseData->total_purchase_cost ?? 0,
                'avg_unit_cost' => $purchaseData->avg_unit_cost ?? 0,
            ],
            'turnover' => $inventoryTurnover,
        ];
    }

    /**
     * Get inventory analytics
     */
    private static function getInventoryAnalytics(): array
    {
        $variants = ProductVariant::with('product')
            ->select(['id', 'product_id', 'variant_name', 'sku', 'quantity_on_hand', 'cost_price', 'target_price', 'is_active'])
            ->get();

        $stockDistribution = [
            'out_of_stock' => 0,
            'low_stock' => 0,
            'normal_stock' => 0,
            'overstock' => 0,
        ];

        $valueDistribution = [
            'low_value' => 0,    // <$100
            'medium_value' => 0, // $100-$500
            'high_value' => 0,   // $500-$1000
            'very_high_value' => 0, // >$1000
        ];

        $ageDistribution = [
            'new' => 0,      // < 30 days
            'fresh' => 0,    // 30-90 days
            'normal' => 0,   // 90-180 days
            'old' => 0,       // > 180 days
        ];

        foreach ($variants as $variant) {
            // Stock distribution
            if ($variant->quantity_on_hand <= 0) {
                $stockDistribution['out_of_stock']++;
            } elseif ($variant->quantity_on_hand <= $variant->reorder_level) {
                $stockDistribution['low_stock']++;
            } elseif ($variant->quantity_on_hand <= 50) {
                $stockDistribution['normal_stock']++;
            } else {
                $stockDistribution['overstock']++;
            }

            // Value distribution
            $value = $variant->quantity_on_hand * $variant->getEffectiveTargetPrice();
            if ($value < 100) {
                $valueDistribution['low_value']++;
            } elseif ($value < 500) {
                $valueDistribution['medium_value']++;
            } elseif ($value < 1000) {
                $valueDistribution['high_value']++;
            } else {
                $valueDistribution['very_high_value']++;
            }

            // Age distribution
            $daysInStock = $variant->updated_at->diffInDays(now());
            if ($daysInStock < 30) {
                $ageDistribution['new']++;
            } elseif ($daysInStock < 90) {
                $ageDistribution['fresh']++;
            } elseif ($daysInStock < 180) {
                $ageDistribution['normal']++;
            } else {
                $ageDistribution['old']++;
            }
        }

        return [
            'stock_distribution' => $stockDistribution,
            'value_distribution' => $valueDistribution,
            'age_distribution' => $ageDistribution,
            'total_variants' => $variants->count(),
        ];
    }

    /**
     * Get trends data
     */
    private static function getTrendsData(): array
    {
        $days = 30;
        $trends = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');

            $dailyStats = self::getDailyStats($date);

            $trends[] = [
                'date' => $date,
                'sales_revenue' => $dailyStats['sales_revenue'],
                'sales_quantity' => $dailyStats['sales_quantity'],
                'purchases_cost' => $dailyStats['purchases_cost'],
                'profit' => $dailyStats['profit'],
            ];
        }

        return $trends;
    }

    /**
     * Get daily statistics for a specific date
     */
    private static function getDailyStats(string $date): array
    {
        $salesData = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->whereDate('sales_orders.order_date', $date)
            ->whereNotNull('sales_order_items.variant_id')
            ->select([
                DB::raw('SUM(sales_order_items.final_price) as sales_revenue'),
                DB::raw('SUM(sales_order_items.quantity) as sales_quantity'),
                DB::raw('SUM(sales_order_items.profit_amount) as profit'),
            ])
            ->first();

        $purchaseData = PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->whereDate('purchase_orders.order_date', $date)
            ->whereNotNull('purchase_order_items.variant_id')
            ->select([
                DB::raw('SUM(purchase_order_items.total_price) as purchases_cost'),
            ])
            ->first();

        return [
            'sales_revenue' => $salesData->sales_revenue ?? 0,
            'sales_quantity' => $salesData->sales_quantity ?? 0,
            'purchases_cost' => $purchaseData->purchases_cost ?? 0,
            'profit' => ($salesData->profit ?? 0) - ($purchaseData->purchases_cost ?? 0),
        ];
    }

    /**
     * Get alerts and notifications
     */
    private static function getAlerts(): array
    {
        $alerts = [];

        // Out of stock alerts
        $outOfStock = ProductVariant::where('quantity_on_hand', '<=', 0)
            ->where('is_active', true)
            ->with('product')
            ->limit(10)
            ->get();

        if ($outOfStock->isNotEmpty()) {
            $alerts[] = [
                'type' => 'critical',
                'title' => 'Out of Stock Variants',
                'count' => $outOfStock->count(),
                'items' => $outOfStock->map(function($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->product->name . ' (' . $variant->variant_name . ')',
                        'sku' => $variant->sku,
                        'last_updated' => $variant->updated_at->format('Y-m-d H:i'),
                    ];
                })->toArray(),
            ];
        }

        // Low stock alerts
        $lowStock = ProductVariant::whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->where('quantity_on_hand', '>', 0)
            ->where('is_active', true)
            ->with('product')
            ->orderByRaw('(quantity_on_hand / GREATEST(reorder_level, 1))')
            ->limit(10)
            ->get();

        if ($lowStock->isNotEmpty()) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Stock Variants',
                'count' => $lowStock->count(),
                'items' => $lowStock->map(function($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->product->name . ' (' . $variant->variant_name . ')',
                        'sku' => $variant->sku,
                        'current_stock' => $variant->quantity_on_hand,
                        'reorder_level' => $variant->reorder_level,
                        'days_of_stock' => $variant->reorder_level > 0 ?
                            floor($variant->quantity_on_hand / ($variant->daily_sales_volume ?? 1)) : 0,
                    ];
                })->toArray(),
            ];
        }

        // Performance alerts
        $poorPerformers = self::getPoorPerformingVariants();
        if ($poorPerformers->isNotEmpty()) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Poor Performing Variants',
                'count' => $poorPerformers->count(),
                'items' => $poorPerformers->take(5)->toArray(),
            ];
        }

        return $alerts;
    }

    /**
     * Get top performing variants
     */
    private static function getTopPerformers(): array
    {
        $last30Days = now()->subDays(30);

        return [
            'top_selling' => self::getTopSellingVariants($last30Days),
            'most_profitable' => self::getMostProfitableVariants($last30Days),
            'highest_value' => self::getHighestValueVariants(),
            'fastest_turning' => self::getFastestTurningVariants($last30Days),
        ];
    }

    /**
     * Calculate total stock value
     */
    private static function calculateTotalStockValue(): float
    {
        return ProductVariant::where('quantity_on_hand', '>', 0)
            ->get()
            ->sum(function($variant) {
                return $variant->quantity_on_hand * $variant->getEffectiveTargetPrice();
            });
    }

    /**
     * Calculate inventory turnover
     */
    private static function calculateInventoryTurnover(): array
    {
        $totalStockValue = self::calculateTotalStockValue();
        $last30DaysRevenue = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.order_date', '>=', now()->subDays(30))
            ->whereNotNull('sales_order_items.variant_id')
            ->sum('sales_order_items.final_price');

        return [
            'turnover_rate' => $totalStockValue > 0 ? ($last30DaysRevenue * 12) / $totalStockValue : 0, // Annualized
            'days_of_supply' => $last30DaysRevenue > 0 ? ($totalStockValue * 365) / ($last30DaysRevenue * 12) : 0,
            'stock_value' => $totalStockValue,
            'monthly_revenue' => $last30DaysRevenue,
        ];
    }

    /**
     * Get top selling variants
     */
    private static function getTopSellingVariants($startDate): \Illuminate\Support\Collection
    {
        return SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('product_variants', 'sales_order_items.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('sales_orders.order_date', '>=', $startDate)
            ->select([
                'product_variants.id',
                'product_variants.variant_name',
                'product_variants.sku',
                'products.name as product_name',
                DB::raw('SUM(sales_order_items.quantity) as total_quantity'),
                DB::raw('SUM(sales_order_items.final_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sales_orders.id) as order_count'),
            ])
            ->groupBy('product_variants.id', 'product_variants.variant_name', 'product_variants.sku', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get most profitable variants
     */
    private static function getMostProfitableVariants($startDate): \Illuminate\Support\Collection
    {
        return SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('product_variants', 'sales_order_items.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('sales_orders.order_date', '>=', $startDate)
            ->select([
                'product_variants.id',
                'product_variants.variant_name',
                'product_variants.sku',
                'products.name as product_name',
                DB::raw('SUM(sales_order_items.profit_amount) as total_profit'),
                DB::raw('SUM(sales_order_items.final_price) as total_revenue'),
                DB::raw('(SUM(sales_order_items.profit_amount) / SUM(sales_order_items.final_price)) * 100 as profit_margin'),
            ])
            ->groupBy('product_variants.id', 'product_variants.variant_name', 'product_variants.sku', 'products.name')
            ->having('total_revenue', '>', 0)
            ->orderBy('total_profit', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get highest value variants
     */
    private static function getHighestValueVariants(): \Illuminate\Support\Collection
    {
        return ProductVariant::with('product')
            ->select([
                'id', 'variant_name', 'sku', 'quantity_on_hand', 'target_price',
                DB::raw('(quantity_on_hand * target_price) as total_value'),
            ])
            ->where('quantity_on_hand', '>', 0)
            ->where('is_active', true)
            ->orderBy('total_value', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get fastest turning variants
     */
    private static function getFastestTurningVariants($startDate): \Illuminate\Support\Collection
    {
        // Calculate days of inventory based on recent sales
        $variantSales = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('product_variants', 'sales_order_items.variant_id', '=', 'product_variants.id')
            ->where('sales_orders.order_date', '>=', $startDate)
            ->select([
                'product_variants.id',
                'product_variants.variant_name',
                DB::raw('SUM(sales_order_items.quantity) as total_sold'),
                DB::raw('AVG(sales_order_items.quantity) as daily_sales_volume'),
            ])
            ->groupBy('product_variants.id', 'product_variants.variant_name')
            ->get();

        return $variantSales->map(function($variant) {
            $daysOfSupply = $variant->daily_sales_volume > 0 ?
                $variant->quantity_on_hand / $variant->daily_sales_volume : 999;

            return [
                'id' => $variant->id,
                'variant_name' => $variant->variant_name,
                'total_sold' => $variant->total_sold,
                'daily_sales_volume' => $variant->daily_sales_volume,
                'days_of_supply' => $daysOfSupply,
            ];
        })->sortBy('days_of_supply')->take(10)->values();
    }

    /**
     * Get poor performing variants
     */
    private static function getPoorPerformingVariants(): \Illuminate\Support\Collection
    {
        $last30Days = now()->subDays(30);

        return SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('product_variants', 'sales_order_items.variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->where('sales_orders.order_date', '>=', $last30Days)
            ->select([
                'product_variants.id',
                'product_variants.variant_name',
                'product_variants.sku',
                'products.name as product_name',
                DB::raw('SUM(sales_order_items.quantity) as total_quantity'),
                DB::raw('(SUM(sales_order_items.profit_amount) / SUM(sales_order_items.final_price)) * 100 as profit_margin'),
            ])
            ->groupBy('product_variants.id', 'product_variants.variant_name', 'product_variants.sku', 'products.name')
            ->having('total_quantity', '>=', 5) // At least 5 sales to be meaningful
            ->orderBy('profit_margin', 'asc')
            ->limit(20)
            ->get();
    }

    /**
     * Get real-time analytics data
     */
    public static function getRealTimeMetrics(): array
    {
        $now = now();

        return [
            'current_time' => $now->toISOString(),
            'active_sessions' => self::getActiveUserSessions(),
            'cache_performance' => self::getCachePerformance(),
            'database_performance' => self::getDatabasePerformance(),
            'variant_activity' => self::getVariantActivity($now),
        ];
    }

    /**
     * Get active user sessions
     */
    private static function getActiveUserSessions(): array
    {
        // This would need to be implemented based on your session management system
        return [
            'total_sessions' => 0,
            'active_sessions' => 0,
            'peak_sessions_today' => 0,
        ];
    }

    /**
     * Get cache performance metrics
     */
    private static function getCachePerformance(): array
    {
        try {
            $stats = VariantCacheService::getCacheStatistics();

            return [
                'total_keys' => $stats['total_variant_keys'] ?? 0,
                'memory_usage' => $stats['memory_usage'] ?? 0,
                'hit_rate' => 0, // Would need to implement hit rate calculation
                'oldest_key_age' => $stats['oldest_key_age_seconds'] ?? 0,
                'newest_key_age' => $stats['newest_key_age_seconds'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Cache monitoring unavailable',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get database performance metrics
     */
    private static function getDatabasePerformance(): array
    {
        try {
            // This would require the performance_schema to be enabled
            $slowQueries = DB::select('
                SELECT
                    DIGEST_TEXT as query,
                    TIMER_WAIT/1000000 as execution_time
                FROM performance_schema.events_statements_summary
                WHERE TIMER_WAIT > 100
                ORDER BY TIMER_WAIT DESC
                LIMIT 5
            ');

            return [
                'slow_queries_count' => count($slowQueries),
                'slow_queries' => $slowQueries,
                'connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 0,
                'queries_per_second' => 0, // Would need to calculate this
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Database monitoring unavailable',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent variant activity
     */
    private static function getVariantActivity($currentTime): array
    {
        $recentActivity = [];

        // Recent sales
        $recentSales = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('product_variants', 'sales_order_items.variant_id', '=', 'product_variants.id')
            ->where('sales_orders.created_at', '>=', $currentTime->subMinutes(30))
            ->orderBy('sales_orders.created_at', 'desc')
            ->limit(5)
            ->get(['sales_orders.created_at', 'product_variants.variant_name']);

        // Recent stock movements
        $recentMovements = StockMovement::where('created_at', '>=', $currentTime->subMinutes(30))
            ->whereNotNull('variant_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['created_at', 'movement_type', 'quantity', 'notes']);

        return [
            'recent_sales' => $recentSales->map(function($item) {
                return [
                    'time' => $item->created_at->toISOString(),
                    'type' => 'sale',
                    'variant' => $item->variant_name,
                ];
            }),
            'recent_movements' => $recentMovements->map(function($item) {
                return [
                    'time' => $item->created_at->toISOString(),
                    'type' => $item->movement_type,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                ];
            }),
        ];
    }

    /**
     * Export analytics data to CSV
     */
    public static function exportAnalytics(string $type = 'overview', ?array $filters = null): string
    {
        $filename = "variant_analytics_{$type}_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $path = storage_path("exports/{$filename}");

        switch ($type) {
            case 'overview':
                $data = self::exportOverviewData();
                break;
            case 'performance':
                $data = self::exportPerformanceData();
                break;
            case 'inventory':
                $data = self::exportInventoryData();
                break;
            default:
                throw new \InvalidArgumentException("Invalid export type: {$type}");
        }

        // Create CSV file
        $file = fopen($path, 'w');
        fputcsv($file, array_keys((array) $data['headers']));

        foreach ($data['data'] as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return $filename;
    }

    /**
     * Export overview data
     */
    private static function exportOverviewData(): array
    {
        $overview = self::getOverviewStats();

        return [
            'headers' => ['Metric', 'Value', 'Unit'],
            'data' => [
                ['Total Variants', $overview['total_variants'], 'Count'],
                ['Active Variants', $overview['active_variants'], 'Count'],
                ['Variants with Stock', $overview['total_variants_with_stock'], 'Count'],
                ['Out of Stock', $overview['total_variants_out_of_stock'], 'Count'],
                ['Low Stock Variants', $overview['low_stock_variants'], 'Count'],
                ['Total Stock Value', $overview['total_stock_value'], 'Currency'],
                ['Average Variant Price', $overview['average_variant_price'], 'Currency'],
                ['Products with Variants', $overview['total_products_with_variants'], 'Count'],
            ],
        ];
    }

    /**
     * Export performance data
     */
    private static function exportPerformanceData(): array
    {
        $performance = self::getPerformanceMetrics();

        $data = [
            ['Metric', 'Value', 'Unit'],
            ['Variants Sold (30d)', $performance['sales']['variants_sold'], 'Count'],
            ['Total Quantity Sold (30d)', $performance['sales']['total_quantity_sold'], 'Units'],
            ['Total Revenue (30d)', $performance['sales']['total_revenue'], 'Currency'],
            ['Total Profit (30d)', $performance['sales']['total_profit'], 'Currency'],
            ['Average Unit Price', $performance['sales']['avg_unit_price'], 'Currency'],
            ['Profit Margin', $performance['sales']['profit_margin'], 'Percentage'],
            ['Variants Purchased (30d)', $performance['purchases']['variants_purchased'], 'Count'],
            ['Total Quantity Purchased (30d)', $performance['purchases']['total_quantity_purchased'], 'Units'],
            ['Total Purchase Cost (30d)', $performance['purchases']['total_purchase_cost'], 'Currency'],
            ['Average Unit Cost', $performance['purchases']['avg_unit_cost'], 'Currency'],
            ['Turnover Rate (Annual)', $performance['turnover']['turnover_rate'], 'Ratio'],
            ['Days of Supply', $performance['turnover']['days_of_supply'], 'Days'],
        ];

        return [
            'headers' => array_column($data, 0),
            'data' => array_map('array_values', array_slice($data, 1)),
        ];
    }

    /**
     * Export inventory data
     */
    private static function exportInventoryData(): array
    {
        $inventory = self::getInventoryAnalytics();

        $data = [
            ['Category', 'Count', 'Percentage'],
            ['Out of Stock', $inventory['stock_distribution']['out_of_stock'], 'Count'],
            ['Low Stock', $inventory['stock_distribution']['low_stock'], 'Count'],
            ['Normal Stock', $inventory['stock_distribution']['normal_stock'], 'Count'],
            ['Overstock', $inventory['stock_distribution']['overstock'], 'Count'],
            ['Low Value (<$100)', $inventory['value_distribution']['low_value'], 'Count'],
            ['Medium Value ($100-$500)', $inventory['value_distribution']['medium_value'], 'Count'],
            ['High Value ($500-$1000)', $inventory['value_distribution']['high_value'], 'Count'],
            ['Very High Value (>$1000)', $inventory['value_distribution']['very_high_value'], 'Count'],
            ['New (< 30 days)', $inventory['age_distribution']['new'], 'Count'],
            ['Fresh (30-90 days)', $inventory['age_distribution']['fresh'], 'Count'],
            ['Normal (90-180 days)', $inventory['age_distribution']['normal'], 'Count'],
            ['Old (>180 days)', $inventory['age_distribution']['old'], 'Count'],
        ];

        return [
            'headers' => array_column($data, 0),
            'data' => array_map('array_values', $data),
        ];
    }
}