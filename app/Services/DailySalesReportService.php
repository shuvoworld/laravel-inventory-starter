<?php

namespace App\Services;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailySalesReportService
{
    /**
     * Generate daily sales report for a specific date
     */
    public function generateDailyReport(?Carbon $date = null): array
    {
        $reportDate = $date ?? Carbon::today();

        // Get sales orders for the specific date
        $salesOrders = $this->getSalesOrdersForDate($reportDate);

        // Calculate key metrics
        $totalRevenue = $salesOrders->sum('total_amount');
        $totalTransactions = $salesOrders->count();
        $averageTransactionValue = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Get top selling products
        $topProducts = $this->getTopSellingProducts($reportDate, 3);

        // Generate performance summary
        $performanceSummary = $this->generatePerformanceSummary($totalRevenue, $totalTransactions, $averageTransactionValue);

        return [
            'report_date' => $reportDate->format('F j, Y'),
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'average_transaction_value' => $averageTransactionValue,
            'top_products' => $topProducts,
            'performance_summary' => $performanceSummary,
            'formatted_metrics' => [
                'total_revenue' => number_format($totalRevenue, 2),
                'total_transactions' => number_format($totalTransactions),
                'average_transaction_value' => number_format($averageTransactionValue, 2),
            ]
        ];
    }

    /**
     * Get sales orders for a specific date
     */
    private function getSalesOrdersForDate(Carbon $date): Collection
    {
        return SalesOrder::whereDate('order_date', $date)
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->with(['items.product'])
            ->get();
    }

    /**
     * Get top selling products by quantity for a specific date
     */
    private function getTopSellingProducts(Carbon $date, int $limit = 3): Collection
    {
        return SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->whereDate('sales_orders.order_date', $date)
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->selectRaw('
                products.name as product_name,
                SUM(sales_order_items.quantity) as total_quantity,
                SUM(sales_order_items.final_price) as total_revenue,
                COUNT(DISTINCT sales_orders.id) as orders_count
            ')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate a one-sentence performance summary
     */
    private function generatePerformanceSummary(float $revenue, int $transactions, float $avgValue): string
    {
        if ($transactions === 0) {
            return "No sales transactions were recorded for this day.";
        }

        $revenueLevel = $this->categorizeRevenue($revenue);
        $performanceAdjective = $this->getPerformanceAdjective($revenue, $transactions);

        return "Today was a {$performanceAdjective} day with {$transactions} transactions generating {$revenueLevel} of ${$revenue}, averaging ${$avgValue} per sale.";
    }

    /**
     * Categorize revenue level
     */
    private function categorizeRevenue(float $revenue): string
    {
        if ($revenue >= 10000) return "strong revenue";
        if ($revenue >= 5000) return "good revenue";
        if ($revenue >= 1000) return "moderate revenue";
        if ($revenue >= 500) return "decent revenue";
        return "modest revenue";
    }

    /**
     * Get performance adjective based on metrics
     */
    private function getPerformanceAdjective(float $revenue, int $transactions): string
    {
        if ($revenue >= 10000 && $transactions >= 20) return "excellent";
        if ($revenue >= 5000 && $transactions >= 10) return "strong";
        if ($revenue >= 1000 && $transactions >= 5) return "solid";
        if ($transactions >= 3) return "steady";
        return "quiet";
    }

    /**
     * Get daily sales trends for the past week
     */
    public function getWeeklyTrends(): array
    {
        $trends = [];
        $startDate = Carbon::today()->subDays(6);

        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $report = $this->generateDailyReport($date);

            $trends[] = [
                'date' => $date->format('M j'),
                'revenue' => $report['total_revenue'],
                'transactions' => $report['total_transactions'],
                'avg_value' => $report['average_transaction_value'],
            ];
        }

        return $trends;
    }
}