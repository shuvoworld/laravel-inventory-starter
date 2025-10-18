<?php

namespace App\Services;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WeeklyProductPerformanceService
{
    /**
     * Generate weekly product performance report
     */
    public function generateWeeklyReport(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfWeek();
        $endDate = $endDate ?? Carbon::now()->endOfWeek();

        // Get best sellers and slow movers
        $bestSellers = $this->getBestSellers($startDate, $endDate, 5);
        $slowMovers = $this->getSlowMovers($startDate, $endDate, 5);

        // Generate recommendations
        $recommendations = $this->generateRecommendations($slowMovers);

        return [
            'period' => [
                'start_date' => $startDate->format('F j, Y'),
                'end_date' => $endDate->format('F j, Y'),
                'formatted_range' => $startDate->format('M j') . ' - ' . $endDate->format('M j, Y'),
                'week_number' => $startDate->weekOfYear,
                'year' => $startDate->year,
            ],
            'best_sellers' => $bestSellers,
            'slow_movers' => $slowMovers,
            'recommendations' => $recommendations,
            'summary' => [
                'total_products_sold' => $bestSellers->sum('total_quantity'),
                'total_revenue' => $bestSellers->sum('total_revenue'),
                'unique_products' => $bestSellers->count(),
            ]
        ];
    }

    /**
     * Get top selling products by revenue
     */
    private function getBestSellers(Carbon $startDate, Carbon $endDate, int $limit = 5): Collection
    {
        return SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.sku as product_sku,
                SUM(sales_order_items.quantity) as total_quantity,
                SUM(sales_order_items.final_price) as total_revenue,
                COUNT(DISTINCT sales_orders.id) as orders_count,
                AVG(sales_order_items.unit_price) as average_price,
                SUM(sales_order_items.profit_amount) as total_profit
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'product_sku' => $product->product_sku,
                    'total_quantity' => (int) $product->total_quantity,
                    'total_revenue' => round($product->total_revenue, 2),
                    'orders_count' => (int) $product->orders_count,
                    'average_price' => round($product->average_price, 2),
                    'total_profit' => round($product->total_profit, 2),
                    'profit_margin' => $product->total_revenue > 0 ? round(($product->total_profit / $product->total_revenue) * 100, 1) : 0,
                ];
            });
    }

    /**
     * Get slow moving products (lowest sales quantity)
     */
    private function getSlowMovers(Carbon $startDate, Carbon $endDate, int $limit = 5): Collection
    {
        return SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.sku as product_sku,
                SUM(sales_order_items.quantity) as total_quantity,
                SUM(sales_order_items.final_price) as total_revenue,
                COUNT(DISTINCT sales_orders.id) as orders_count,
                products.cost_price,
                products.price
            ')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.cost_price', 'products.price')
            ->orderBy('total_quantity', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($product) {
                return [
                    'product_id' => $product->product_id,
                    'product_name' => $product->product_name,
                    'product_sku' => $product->product_sku,
                    'total_quantity' => (int) $product->total_quantity,
                    'total_revenue' => round($product->total_revenue, 2),
                    'orders_count' => (int) $product->orders_count,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->price,
                    'markup_percentage' => $product->cost_price > 0 ? round((($product->price - $product->cost_price) / $product->cost_price) * 100, 1) : 0,
                ];
            });
    }

    /**
     * Generate recommendations for slow-moving products
     */
    private function generateRecommendations(Collection $slowMovers): array
    {
        $recommendations = [];

        foreach ($slowMovers as $product) {
            $productRecommendations = [];

            // Price-based recommendations
            if ($product['markup_percentage'] > 100) {
                $productRecommendations[] = "Consider a temporary price reduction or promotional discount";
            } elseif ($product['markup_percentage'] < 20) {
                $productRecommendations[] = "Review pricing strategy - low margins may indicate uncompetitive pricing";
            }

            // Sales pattern recommendations
            if ($product['total_quantity'] === 0) {
                $productRecommendations[] = "No sales this period - consider featuring in marketing campaigns";
            } elseif ($product['orders_count'] === 1 && $product['total_quantity'] <= 2) {
                $productRecommendations[] = "Single order with low quantity - investigate customer interest";
            }

            // General recommendations based on quantity
            if ($product['total_quantity'] <= 2) {
                $productRecommendations[] = "Bundle with popular products to increase visibility";
                $productRecommendations[] = "Feature in store displays or homepage banners";
            }

            // Add product-specific recommendation if we have any
            if (!empty($productRecommendations)) {
                $recommendations[] = [
                    'product_name' => $product['product_name'],
                    'product_sku' => $product['product_sku'],
                    'issue' => $product['total_quantity'] === 0 ? 'No sales' : "Only {$product['total_quantity']} unit(s) sold",
                    'recommendations' => array_unique($productRecommendations),
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Get overall weekly summary for slow-movers
     */
    public function getSlowMoversSummary(Collection $slowMovers): string
    {
        $totalSlowMovers = $slowMovers->count();
        $totalQuantitySold = $slowMovers->sum('total_quantity');
        $totalRevenue = $slowMovers->sum('total_revenue');
        $zeroSalesCount = $slowMovers->where('total_quantity', 0)->count();

        if ($totalSlowMovers === 0) {
            return "All products performed well this week with no slow-moving items identified.";
        }

        if ($zeroSalesCount > 0) {
            $summary = "This week, {$zeroSalesCount} out of {$totalSlowMovers} monitored products had zero sales, ";
            $summary .= "while the remaining " . ($totalSlowMovers - $zeroSalesCount) . " products had minimal sales. ";
        } else {
            $summary = "This week, {$totalSlowMovers} products showed minimal sales activity with only {$totalQuantitySold} total units sold. ";
        }

        $summary .= "To boost sales for these slow-moving items, consider implementing targeted promotional strategies such as ";
        $summary .= "bundle deals with popular products, temporary price reductions, featured placement in marketing campaigns, ";
        $summary .= "or creating special displays to increase visibility. ";

        if ($zeroSalesCount > 0) {
            $summary .= "For products with zero sales, investigate market demand and consider whether they need to be repriced, ";
            $summary .= "repositioned, or potentially discontinued to free up inventory space for better-performing items.";
        }

        return $summary;
    }

    /**
     * Get comparison with previous week
     */
    public function getWeeklyComparison(Carbon $currentWeekStart): array
    {
        $currentWeekEnd = $currentWeekStart->copy()->endOfWeek();
        $previousWeekStart = $currentWeekStart->copy()->subWeek()->startOfWeek();
        $previousWeekEnd = $previousWeekStart->copy()->endOfWeek();

        $currentReport = $this->generateWeeklyReport($currentWeekStart, $currentWeekEnd);
        $previousReport = $this->generateWeeklyReport($previousWeekStart, $previousWeekEnd);

        return [
            'revenue_change' => $currentReport['summary']['total_revenue'] - $previousReport['summary']['total_revenue'],
            'revenue_change_percent' => $previousReport['summary']['total_revenue'] > 0
                ? round((($currentReport['summary']['total_revenue'] - $previousReport['summary']['total_revenue']) / $previousReport['summary']['total_revenue']) * 100, 1)
                : 0,
            'quantity_change' => $currentReport['summary']['total_products_sold'] - $previousReport['summary']['total_products_sold'],
            'products_change' => $currentReport['summary']['unique_products'] - $previousReport['summary']['unique_products'],
        ];
    }
}