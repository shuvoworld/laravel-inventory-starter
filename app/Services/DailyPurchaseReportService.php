<?php

namespace App\Services;

use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\Products\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyPurchaseReportService
{
    /**
     * Generate daily purchase report for a specific date
     */
    public function generateDailyReport(?Carbon $date = null): array
    {
        $reportDate = $date ?? Carbon::today();

        // Get purchase orders for the specific date
        $purchaseOrders = $this->getPurchaseOrdersForDate($reportDate);

        // Calculate key metrics
        $totalAmount = $purchaseOrders->sum('total_amount');
        $totalSubtotal = $purchaseOrders->sum('subtotal');
        $totalDiscount = $purchaseOrders->sum('discount_amount');
        $totalTax = $purchaseOrders->sum('tax_amount');
        $totalTransactions = $purchaseOrders->count();
        $averageTransactionValue = $totalTransactions > 0 ? $totalAmount / $totalTransactions : 0;

        // Get top purchased products
        $topProducts = $this->getTopPurchasedProducts($reportDate, 5);

        // Generate performance summary
        $performanceSummary = $this->generatePerformanceSummary($totalAmount, $totalTransactions, $averageTransactionValue);

        return [
            'report_date' => $reportDate->format('F j, Y'),
            'total_amount' => $totalAmount,
            'total_subtotal' => $totalSubtotal,
            'total_discount' => $totalDiscount,
            'total_tax' => $totalTax,
            'total_transactions' => $totalTransactions,
            'average_transaction_value' => $averageTransactionValue,
            'top_products' => $topProducts,
            'performance_summary' => $performanceSummary,
            'formatted_metrics' => [
                'total_amount' => number_format($totalAmount, 2),
                'total_discount' => number_format($totalDiscount, 2),
                'total_transactions' => number_format($totalTransactions),
                'average_transaction_value' => number_format($averageTransactionValue, 2),
            ]
        ];
    }

    /**
     * Get purchase orders for a specific date
     */
    private function getPurchaseOrdersForDate(Carbon $date): Collection
    {
        return PurchaseOrder::whereDate('order_date', $date)
            ->whereIn('status', ['confirmed', 'processing', 'received'])
            ->with(['items.product'])
            ->get();
    }

    /**
     * Get top purchased products by quantity for a specific date
     */
    private function getTopPurchasedProducts(Carbon $date, int $limit = 5): Collection
    {
        return PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->join('products', 'purchase_order_items.product_id', '=', 'products.id')
            ->whereDate('purchase_orders.order_date', $date)
            ->whereIn('purchase_orders.status', ['confirmed', 'processing', 'received'])
            ->selectRaw('
                products.name as product_name,
                SUM(purchase_order_items.quantity) as total_quantity,
                SUM(purchase_order_items.total_price) as total_cost,
                COUNT(DISTINCT purchase_orders.id) as orders_count,
                AVG(purchase_order_items.unit_price) as avg_unit_price
            ')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate a one-sentence performance summary
     */
    private function generatePerformanceSummary(float $amount, int $transactions, float $avgValue): string
    {
        if ($transactions === 0) {
            return "No purchase transactions were recorded for this day.";
        }

        $amountLevel = $this->categorizeAmount($amount);
        $performanceAdjective = $this->getPerformanceAdjective($amount, $transactions);

        return "Today was a {$performanceAdjective} day with {$transactions} purchase orders for {$amountLevel} of \${$amount}, averaging \${$avgValue} per order.";
    }

    /**
     * Categorize purchase amount level
     */
    private function categorizeAmount(float $amount): string
    {
        if ($amount >= 10000) return "strong purchases";
        if ($amount >= 5000) return "good purchases";
        if ($amount >= 1000) return "moderate purchases";
        if ($amount >= 500) return "decent purchases";
        return "modest purchases";
    }

    /**
     * Get performance adjective based on metrics
     */
    private function getPerformanceAdjective(float $amount, int $transactions): string
    {
        if ($amount >= 10000 && $transactions >= 20) return "excellent";
        if ($amount >= 5000 && $transactions >= 10) return "strong";
        if ($amount >= 1000 && $transactions >= 5) return "solid";
        if ($transactions >= 3) return "steady";
        return "quiet";
    }

    /**
     * Get daily purchase trends for the past 30 days
     */
    public function getMonthlyTrends(): array
    {
        $trends = [];
        $startDate = Carbon::today()->subDays(29);

        for ($i = 0; $i < 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            $report = $this->generateDailyReport($date);

            $trends[] = [
                'date' => $date->format('M j'),
                'date_full' => $date->format('Y-m-d'),
                'subtotal' => $report['total_subtotal'],
                'discount' => $report['total_discount'],
                'tax' => $report['total_tax'],
                'amount' => $report['total_amount'],
                'transactions' => $report['total_transactions'],
                'avg_value' => $report['average_transaction_value'],
            ];
        }

        return $trends;
    }
}