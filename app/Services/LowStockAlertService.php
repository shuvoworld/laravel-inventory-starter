<?php

namespace App\Services;

use App\Modules\Products\Models\Product;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\SalesOrder\Models\SalesOrder;
use App\Services\StockCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LowStockAlertService
{
    /**
     * Generate low stock alert report
     */
    public function generateLowStockAlert(int $threshold = 10): array
    {
        // Get products with stock below threshold
        $lowStockProducts = $this->getLowStockProducts($threshold);

        // Calculate days of stock remaining for each product
        $lowStockWithDays = $lowStockProducts->map(function ($product) {
            // Use StockCalculationService for accurate stock from movements
            $currentStock = StockCalculationService::getStockForProduct($product->id);
            $averageDailySales = $this->calculateAverageDailySales($product->id, 30);
            $daysOfStock = $this->calculateDaysOfStockRemaining($currentStock, $averageDailySales);

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'current_stock' => (int) $currentStock,
                'reorder_level' => (int) $product->reorder_level,
                'unit_price' => (float) $product->price,
                'cost_price' => (float) $product->cost_price,
                'average_daily_sales' => round($averageDailySales, 2),
                'days_of_stock_remaining' => $daysOfStock,
                'stock_status' => $this->getStockStatus($currentStock, $daysOfStock),
                'urgency_level' => $this->getUrgencyLevel($currentStock, $daysOfStock),
                'brand_name' => $product->brand ? $product->brand->name : 'No Brand',
                'estimated_stockout_date' => $this->getEstimatedStockoutDate($daysOfStock),
                'recommended_reorder_quantity' => $this->calculateRecommendedReorderQuantity($product, $averageDailySales, $currentStock),
            ];
        });

        // Sort by urgency (most critical first)
        $sortedByUrgency = $lowStockWithDays->sortBy(function ($product) {
            return $product['days_of_stock_remaining'];
        })->values();

        // Identify most critical item
        $mostCriticalItem = $sortedByUrgency->first();

        // Generate summary statistics
        $summary = $this->generateSummary($sortedByUrgency, $threshold);

        return [
            'generated_at' => Carbon::now()->format('F j, Y g:i A'),
            'threshold' => $threshold,
            'low_stock_products' => $sortedByUrgency,
            'most_critical_item' => $mostCriticalItem,
            'summary' => $summary,
        ];
    }

    /**
     * Get products with stock below threshold using movements-based calculation
     */
    private function getLowStockProducts(int $threshold): Collection
    {
        // Get all products and filter by calculated stock from movements
        return Product::where('store_id', auth()->user()->store_id)
            ->with('brand')
            ->get()
            ->filter(function ($product) use ($threshold) {
                // Use StockCalculationService for accurate stock from movements
                $currentStock = StockCalculationService::getStockForProduct($product->id);
                $reorderLevel = $product->reorder_level ?? 10;

                return $currentStock <= $threshold || $currentStock <= $reorderLevel;
            })
            ->values();
    }

    /**
     * Calculate average daily sales for a product over a specified period
     */
    private function calculateAverageDailySales(int $productId, int $days): float
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $totalQuantity = SalesOrderItem::join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_order_items.product_id', $productId)
            ->whereBetween('sales_orders.order_date', [$startDate, $endDate])
            ->whereIn('sales_orders.status', ['confirmed', 'processing', 'shipped', 'delivered'])
            ->sum('sales_order_items.quantity');

        return $totalQuantity / $days;
    }

    /**
     * Calculate days of stock remaining
     */
    private function calculateDaysOfStockRemaining(int $currentStock, float $averageDailySales): float
    {
        if ($averageDailySales <= 0) {
            return 999; // Essentially infinite if no sales
        }

        return $currentStock / $averageDailySales;
    }

    /**
     * Get stock status description
     */
    private function getStockStatus(int $currentStock, float $daysOfStock): string
    {
        if ($currentStock <= 0) {
            return 'Out of Stock';
        } elseif ($currentStock <= 2) {
            return 'Critical';
        } elseif ($currentStock <= 5) {
            return 'Very Low';
        } elseif ($daysOfStock <= 7) {
            return 'Low (Less than 1 week)';
        } elseif ($daysOfStock <= 30) {
            return 'Moderate (Less than 1 month)';
        } else {
            return 'Adequate';
        }
    }

    /**
     * Get urgency level for reordering
     */
    private function getUrgencyLevel(int $currentStock, float $daysOfStock): string
    {
        if ($currentStock <= 0) {
            return 'Urgent - Out of Stock';
        } elseif ($currentStock <= 2) {
            return 'Critical - Immediate Action Required';
        } elseif ($daysOfStock <= 3) {
            return 'High - Order Within 24 Hours';
        } elseif ($daysOfStock <= 7) {
            return 'Medium - Order This Week';
        } elseif ($daysOfStock <= 14) {
            return 'Low - Order Within 2 Weeks';
        } else {
            return 'Monitor - Plan Reorder';
        }
    }

    /**
     * Get estimated stockout date
     */
    private function getEstimatedStockoutDate(float $daysOfStock): ?string
    {
        if ($daysOfStock >= 999) {
            return null; // No stockout expected
        }

        $stockoutDate = Carbon::now()->addDays($daysOfStock);
        return $stockoutDate->format('M j, Y');
    }

    /**
     * Calculate recommended reorder quantity
     */
    private function calculateRecommendedReorderQuantity(Product $product, float $averageDailySales, int $currentStock): int
    {
        if ($averageDailySales <= 0) {
            return max(10, $product->reorder_level * 2); // Default for non-selling items
        }

        // Calculate 30-day supply plus safety stock
        $monthlyDemand = $averageDailySales * 30;
        $safetyStock = $averageDailySales * 7; // 7 days safety stock
        $recommendedQuantity = $monthlyDemand + $safetyStock - $currentStock;

        return max($product->reorder_level * 2, ceil($recommendedQuantity));
    }

    /**
     * Generate summary statistics
     */
    private function generateSummary(Collection $lowStockProducts, int $threshold): array
    {
        $totalProducts = $lowStockProducts->count();
        $totalStockValue = $lowStockProducts->sum(function ($product) {
            return $product['current_stock'] * $product['cost_price'];
        });

        $outOfStock = $lowStockProducts->where('current_stock', 0)->count();
        $criticalItems = $lowStockProducts->where('current_stock', '<=', 2)->count();
        $urgentItems = $lowStockProducts->filter(function ($product) {
            return $product['days_of_stock_remaining'] <= 3;
        })->count();

        return [
            'total_low_stock_items' => $totalProducts,
            'out_of_stock_count' => $outOfStock,
            'critical_items_count' => $criticalItems,
            'urgent_items_count' => $urgentItems,
            'total_inventory_value_at_risk' => round($totalStockValue, 2),
            'average_days_of_stock' => $totalProducts > 0
                ? round($lowStockProducts->avg('days_of_stock_remaining'), 1)
                : 0,
        ];
    }

    /**
     * Generate critical alert summary message
     */
    public function generateCriticalAlertSummary(array $report): string
    {
        $mostCritical = $report['most_critical_item'];

        if (!$mostCritical) {
            return "Great news! No products are currently below the low stock threshold.";
        }

        $summary = "Low Stock Alert: {$report['summary']['total_low_stock_items']} products need attention";

        if ($report['summary']['out_of_stock_count'] > 0) {
            $summary .= ", including {$report['summary']['out_of_stock_count']} out of stock items";
        }

        $summary .= ". ";

        if ($mostCritical['current_stock'] <= 0) {
            $summary .= "URGENT: {$mostCritical['product_name']} is completely out of stock and needs immediate reordering.";
        } elseif ($mostCritical['days_of_stock_remaining'] <= 1) {
            $summary .= "Most critical: {$mostCritical['product_name']} has only {$mostCritical['current_stock']} unit(s) left and will run out within 24 hours.";
        } else {
            $summary .= "Most critical: {$mostCritical['product_name']} needs to be reordered immediately with only {$mostCritical['current_stock']} unit(s) remaining.";
        }

        return $summary;
    }

    /**
     * Get low stock products by category (for dashboard widgets)
     */
    public function getLowStockByCategory(): array
    {
        $lowStock = $this->getLowStockProducts(10);

        // Calculate stock levels using movements-based data
        $stockLevels = $lowStock->map(function ($product) {
            return [
                'product' => $product,
                'current_stock' => StockCalculationService::getStockForProduct($product->id),
            ];
        });

        return [
            'out_of_stock' => $stockLevels->where('current_stock', 0)->count(),
            'critical' => $stockLevels->where('current_stock', '>', 0)->where('current_stock', '<=', 2)->count(),
            'low' => $stockLevels->where('current_stock', '>', 2)->where('current_stock', '<=', 10)->count(),
            'total' => $stockLevels->count(),
        ];
    }
}