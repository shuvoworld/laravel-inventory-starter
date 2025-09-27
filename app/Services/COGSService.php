<?php

namespace App\Services;

use App\Modules\SalesOrder\Models\SalesOrder;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\Products\Models\Product;
use Carbon\Carbon;

class COGSService
{
    /**
     * Calculate COGS for a specific sales order
     */
    public function calculateOrderCOGS(SalesOrder $order): float
    {
        $cogs = 0;

        foreach ($order->items as $item) {
            $cogs += $item->cogs_amount;
        }

        return $cogs;
    }

    /**
     * Calculate COGS for a period
     */
    public function calculatePeriodCOGS(Carbon $startDate, Carbon $endDate): float
    {
        $salesOrders = SalesOrder::with('items.product')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        $totalCOGS = 0;

        foreach ($salesOrders as $order) {
            $totalCOGS += $this->calculateOrderCOGS($order);
        }

        return $totalCOGS;
    }

    /**
     * Get COGS breakdown by product for a period
     */
    public function getProductCOGSBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        $salesOrders = SalesOrder::with('items.product')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->get();

        $productBreakdown = [];

        foreach ($salesOrders as $order) {
            foreach ($order->items as $item) {
                $productId = $item->product_id;

                if (!isset($productBreakdown[$productId])) {
                    $productBreakdown[$productId] = [
                        'product_id' => $productId,
                        'product_name' => $item->product->name,
                        'sku' => $item->product->sku ?? 'N/A',
                        'quantity_sold' => 0,
                        'total_revenue' => 0,
                        'total_cogs' => 0,
                        'total_profit' => 0,
                        'profit_margin' => 0,
                    ];
                }

                $productBreakdown[$productId]['quantity_sold'] += $item->quantity;
                $productBreakdown[$productId]['total_revenue'] += $item->final_price;
                $productBreakdown[$productId]['total_cogs'] += $item->cogs_amount;
                $productBreakdown[$productId]['total_profit'] += $item->profit_amount;
            }
        }

        // Calculate profit margins
        foreach ($productBreakdown as &$product) {
            if ($product['total_revenue'] > 0) {
                $product['profit_margin'] = ($product['total_profit'] / $product['total_revenue']) * 100;
            }
        }

        return array_values($productBreakdown);
    }

    /**
     * Get COGS trends over time
     */
    public function getCOGSTrends(Carbon $startDate, Carbon $endDate, string $period = 'month'): array
    {
        $trends = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $periodStart = $current->copy();
            $periodEnd = match($period) {
                'day' => $current->copy()->endOfDay(),
                'week' => $current->copy()->endOfWeek(),
                'month' => $current->copy()->endOfMonth(),
                default => $current->copy()->endOfDay(),
            };

            if ($periodEnd > $endDate) {
                $periodEnd = $endDate->copy();
            }

            $cogs = $this->calculatePeriodCOGS($periodStart, $periodEnd);
            $revenue = $this->getRevenueForPeriod($periodStart, $periodEnd);

            $trends[] = [
                'period' => match($period) {
                    'day' => $periodStart->format('M j'),
                    'week' => $periodStart->format('M j') . ' - ' . $periodEnd->format('M j'),
                    'month' => $periodStart->format('M Y'),
                    default => $periodStart->format('M j'),
                },
                'period_start' => $periodStart->format('Y-m-d'),
                'period_end' => $periodEnd->format('Y-m-d'),
                'cogs' => $cogs,
                'revenue' => $revenue,
                'gross_profit' => $revenue - $cogs,
                'gross_profit_margin' => $revenue > 0 ? (($revenue - $cogs) / $revenue) * 100 : 0,
            ];

            $current = match($period) {
                'day' => $current->addDay(),
                'week' => $current->addWeek(),
                'month' => $current->addMonth(),
                default => $current->addDay(),
            };
        }

        return $trends;
    }

    /**
     * Get total revenue for a period
     */
    private function getRevenueForPeriod(Carbon $startDate, Carbon $endDate): float
    {
        return SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->sum('total_amount');
    }

    /**
     * Update product cost prices and recalculate COGS
     */
    public function updateProductCostPrice(Product $product, float $newCostPrice): bool
    {
        $product->cost_price = $newCostPrice;
        $product->calculateProfitMargin();

        // Update existing sales order items that use this product
        $salesOrderItems = SalesOrderItem::where('product_id', $product->id)->get();

        foreach ($salesOrderItems as $item) {
            $item->cost_price = $newCostPrice;
            $item->cogs_amount = $item->quantity * $newCostPrice;
            $item->profit_amount = $item->final_price - $item->cogs_amount;
            $item->save();

            // Recalculate the parent order totals
            if ($item->salesOrder) {
                $item->salesOrder->calculateTotals();
                $item->salesOrder->save();
            }
        }

        return $product->save();
    }

    /**
     * Get COGS summary statistics
     */
    public function getCOGSSummary(Carbon $startDate, Carbon $endDate): array
    {
        $cogs = $this->calculatePeriodCOGS($startDate, $endDate);
        $revenue = $this->getRevenueForPeriod($startDate, $endDate);
        $productBreakdown = $this->getProductCOGSBreakdown($startDate, $endDate);

        return [
            'total_cogs' => $cogs,
            'total_revenue' => $revenue,
            'gross_profit' => $revenue - $cogs,
            'gross_profit_margin' => $revenue > 0 ? (($revenue - $cogs) / $revenue) * 100 : 0,
            'total_products_sold' => count($productBreakdown),
            'average_cogs_per_order' => $this->getAverageCOGSPerOrder($startDate, $endDate),
            'top_profitable_products' => collect($productBreakdown)->sortByDesc('profit_margin')->take(5)->values(),
            'bottom_profitable_products' => collect($productBreakdown)->sortBy('profit_margin')->take(5)->values(),
        ];
    }

    /**
     * Get average COGS per order
     */
    private function getAverageCOGSPerOrder(Carbon $startDate, Carbon $endDate): float
    {
        $orders = SalesOrder::whereBetween('order_date', [$startDate, $endDate])
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->count();

        if ($orders === 0) {
            return 0;
        }

        return $this->calculatePeriodCOGS($startDate, $endDate) / $orders;
    }
}