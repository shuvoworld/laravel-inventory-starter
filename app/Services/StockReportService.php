<?php

namespace App\Services;

use App\Modules\Products\Models\Product;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\SalesOrder\Models\SalesOrder;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\StockCalculationService;

class StockReportService
{
    /**
     * Get comprehensive stock overview for the current store
     */
    public function getStockOverview(): array
    {
        $products = Product::with(['brand'])
            ->where('store_id', auth()->user()->store_id)
            ->get();

        $totalProducts = $products->count();
        $totalStockValue = 0;
        $totalStockQuantity = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;
        $brandsBreakdown = [];

        foreach ($products as $product) {
            // Use StockCalculationService for accurate stock from movements
            $currentStock = StockCalculationService::getStockForProduct($product->id);
            $stockValue = $currentStock * ($product->cost_price ?? 0);

            $totalStockValue += $stockValue;
            $totalStockQuantity += $currentStock;

            if ($currentStock <= 0) {
                $outOfStockCount++;
            } elseif ($currentStock <= ($product->reorder_level ?? 10)) {
                $lowStockCount++;
            }

            // Brand breakdown
            $brandName = $product->brand?->name ?? 'No Brand';
            if (!isset($brandsBreakdown[$brandName])) {
                $brandsBreakdown[$brandName] = [
                    'product_count' => 0,
                    'total_quantity' => 0,
                    'total_value' => 0,
                    'low_stock_count' => 0,
                ];
            }
            $brandsBreakdown[$brandName]['product_count']++;
            $brandsBreakdown[$brandName]['total_quantity'] += $currentStock;
            $brandsBreakdown[$brandName]['total_value'] += $stockValue;
            if ($currentStock <= ($product->reorder_level ?? 10)) {
                $brandsBreakdown[$brandName]['low_stock_count']++;
            }
        }

        return [
            'summary' => [
                'total_products' => $totalProducts,
                'total_stock_quantity' => $totalStockQuantity,
                'total_stock_value' => $totalStockValue,
                'low_stock_count' => $lowStockCount,
                'out_of_stock_count' => $outOfStockCount,
                'healthy_stock_count' => $totalProducts - $lowStockCount - $outOfStockCount,
                'average_cost_per_unit' => $totalStockQuantity > 0 ? $totalStockValue / $totalStockQuantity : 0,
                'source' => 'stock_movements_table', // Indicate data source
            ],
            'brands_breakdown' => $brandsBreakdown,
            'stock_integrity' => StockCalculationService::validateStockIntegrity(),
        ];
    }

    /**
     * Get detailed stock information with filters
     */
    public function getDetailedStock(array $filters = []): Collection
    {
        $query = Product::with(['brand'])
            ->where('store_id', auth()->user()->store_id);

        // Apply filters
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'in_stock':
                    $query->where('quantity_on_hand', '>', 0);
                    break;
                case 'low_stock':
                    $query->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                          ->where('quantity_on_hand', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('quantity_on_hand', '<=', 0);
                    break;
            }
        }

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('sku', 'like', $searchTerm)
                  ->orWhere('description', 'like', $searchTerm);
            });
        }

        return $query->get()->map(function ($product) {
            // Use StockCalculationService for accurate stock from movements
            $currentStock = StockCalculationService::getStockForProduct($product->id);
            $reorderLevel = $product->reorder_level ?? 10;
            $stockStatus = $this->getStockStatus($currentStock, $reorderLevel);

            // Calculate inventory metrics (based on cost_price only, as selling price was removed)
            $totalValue = $currentStock * ($product->cost_price ?? 0);

            // Get recent activity (last 30 days)
            $thirtyDaysAgo = Carbon::now()->subDays(30);
            $recentSales = $product->salesOrderItems()
                ->whereHas('salesOrder', function ($q) use ($thirtyDaysAgo) {
                    $q->where('order_date', '>=', $thirtyDaysAgo);
                })
                ->sum('quantity');

            $recentPurchases = $product->purchaseOrderItems()
                ->whereHas('purchaseOrder', function ($q) use ($thirtyDaysAgo) {
                    $q->where('order_date', '>=', $thirtyDaysAgo);
                })
                ->sum('quantity');

            // Calculate monthly average consumption
            $monthlyConsumption = $recentSales;
            $stockTurnoverRate = $monthlyConsumption > 0 ? $currentStock / $monthlyConsumption : 0;

            // Calculate safety stock recommendation
            $safetyStock = max($reorderLevel, ceil($monthlyConsumption * 1.5));

            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'brand' => $product->brand?->name ?? 'No Brand',
                'current_stock' => $currentStock,
                'reorder_level' => $reorderLevel,
                'stock_status' => $stockStatus,
                'cost_price' => $product->cost_price ?? 0,
                'total_value' => $totalValue,
                'recent_sales' => $recentSales,
                'recent_purchases' => $recentPurchases,
                'monthly_consumption' => $monthlyConsumption,
                'stock_turnover_rate' => $stockTurnoverRate,
                'safety_stock_recommended' => $safetyStock,
                'days_of_inventory' => $monthlyConsumption > 0 ?
                    round(($currentStock / $monthlyConsumption) * 30, 1) : 0,
                'last_updated' => $product->updated_at,
            ];
        });
    }

    /**
     * Get stock movement trends for a given period
     */
    public function getStockMovementTrends(Carbon $startDate, Carbon $endDate): array
    {
        $products = Product::where('store_id', auth()->user()->store_id)->get();
        $trends = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();

            $dayTrends = [
                'date' => $current->format('Y-m-d'),
                'sales_out' => 0,
                'purchases_in' => 0,
                'net_change' => 0,
                'total_products_sold' => 0,
                'total_products_purchased' => 0,
            ];

            foreach ($products as $product) {
                // Get sales for the day
                $daySales = $product->salesOrderItems()
                    ->whereHas('salesOrder', function ($q) use ($dayStart, $dayEnd) {
                        $q->whereBetween('order_date', [$dayStart, $dayEnd]);
                    })
                    ->sum('quantity');

                // Get purchases for the day
                $dayPurchases = $product->purchaseOrderItems()
                    ->whereHas('purchaseOrder', function ($q) use ($dayStart, $dayEnd) {
                        $q->whereBetween('order_date', [$dayStart, $dayEnd]);
                    })
                    ->sum('quantity');

                $dayTrends['sales_out'] += $daySales * ($product->cost_price ?? 0);
                $dayTrends['purchases_in'] += $dayPurchases * ($product->cost_price ?? 0);
                $dayTrends['total_products_sold'] += $daySales;
                $dayTrends['total_products_purchased'] += $dayPurchases;
            }

            $dayTrends['net_change'] = $dayTrends['purchases_in'] - $dayTrends['sales_out'];
            $trends[] = $dayTrends;

            $current->addDay();
        }

        return $trends;
    }

    /**
     * Get products that need reordering
     */
    public function getReorderRecommendations(): Collection
    {
        return Product::with(['brand'])
            ->where('store_id', auth()->user()->store_id)
            ->get() // Get all products and filter by calculated stock
            ->filter(function ($product) {
                // Use StockCalculationService for accurate stock from movements
                $currentStock = StockCalculationService::getStockForProduct($product->id);
                $reorderLevel = $product->reorder_level ?? 10;
                return $currentStock <= $reorderLevel;
            })
            ->map(function ($product) {
                $reorderLevel = $product->reorder_level ?? 10;
                $currentStock = StockCalculationService::getStockForProduct($product->id);
                $monthlyConsumption = $this->calculateMonthlyConsumption($product);

                // Recommended reorder quantity
                $recommendedQuantity = max(
                    $reorderLevel * 2 - $currentStock,
                    ceil($monthlyConsumption * 2)
                );

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'brand' => $product->brand?->name ?? 'No Brand',
                    'current_stock' => $currentStock,
                    'reorder_level' => $reorderLevel,
                    'monthly_consumption' => $monthlyConsumption,
                    'recommended_quantity' => $recommendedQuantity,
                    'estimated_cost' => $recommendedQuantity * ($product->cost_price ?? 0),
                    'urgency' => $this->getReorderUrgency($currentStock, $reorderLevel),
                ];
            })
            ->sortBy('urgency')
            ->values();
    }

    /**
     * Get stock valuation summary
     */
    public function getStockValuation(): array
    {
        $products = Product::where('store_id', auth()->user()->store_id)->get();

        $totalCostValue = 0;
        $totalMarketValue = 0;
        $totalPotentialProfit = 0;
        $valuationByBrand = [];

        foreach ($products as $product) {
            // Use StockCalculationService for accurate stock from movements
            $quantity = StockCalculationService::getStockForProduct($product->id);
            $costPrice = $product->cost_price ?? 0;

            $costValue = $quantity * $costPrice;

            $totalCostValue += $costValue;

            // By brand
            $brand = $product->brand?->name ?? 'No Brand';
            if (!isset($valuationByBrand[$brand])) {
                $valuationByBrand[$brand] = [
                    'cost_value' => 0,
                    'product_count' => 0,
                ];
            }
            $valuationByBrand[$brand]['cost_value'] += $costValue;
            $valuationByBrand[$brand]['product_count']++;
        }

        return [
            'total_cost_value' => $totalCostValue,
            'total_market_value' => 0, // Market value removed as selling price was removed
            'total_potential_profit' => 0, // Potential profit removed as selling price was removed
            'overall_profit_margin' => 0, // Profit margin removed as selling price was removed
            'valuation_by_brand' => $valuationByBrand,
        ];
    }

    /**
     * Helper methods
     */
    private function getStockStatus(int $currentStock, int $reorderLevel): string
    {
        if ($currentStock <= 0) {
            return 'out_of_stock';
        } elseif ($currentStock <= $reorderLevel) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    /**
     * Get comprehensive stock movement analysis
     */
    public function getStockMovementAnalysis(): array
    {
        $products = Product::where('store_id', auth()->user()->store_id)->get();
        $stockData = StockCalculationService::getStockForAllProducts();

        $analysis = [
            'total_products' => $products->count(),
            'products_with_movements' => count($stockData),
            'total_stock_value' => 0,
            'stock_integrity' => StockCalculationService::validateStockIntegrity(),
            'movement_statistics' => StockCalculationService::getMovementStatsByTransactionType(),
            'low_stock_products' => [],
            'out_of_stock_products' => [],
            'high_value_products' => [],
        ];

        foreach ($products as $product) {
            $currentStock = StockCalculationService::getStockForProduct($product->id);
            $stockValue = $currentStock * ($product->cost_price ?? 0);
            $reorderLevel = $product->reorder_level ?? 10;

            $analysis['total_stock_value'] += $stockValue;

            if ($currentStock <= 0) {
                $analysis['out_of_stock_products'][] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $currentStock,
                    'stock_value' => $stockValue,
                ];
            } elseif ($currentStock <= $reorderLevel) {
                $analysis['low_stock_products'][] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $currentStock,
                    'reorder_level' => $reorderLevel,
                    'stock_value' => $stockValue,
                ];
            }

            if ($stockValue > 1000) { // High value threshold
                $analysis['high_value_products'][] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $currentStock,
                    'stock_value' => $stockValue,
                    'cost_price' => $product->cost_price ?? 0,
                ];
            }
        }

        // Sort arrays by value (highest first)
        usort($analysis['high_value_products'], fn($a, $b) => $b['stock_value'] <=> $a['stock_value']);

        return $analysis;
    }

    private function calculateMonthlyConsumption(Product $product): float
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        return $product->salesOrderItems()
            ->whereHas('salesOrder', function ($q) use ($thirtyDaysAgo) {
                $q->where('order_date', '>=', $thirtyDaysAgo);
            })
            ->sum('quantity');
    }

    private function getReorderUrgency(int $currentStock, int $reorderLevel): string
    {
        if ($currentStock <= 0) {
            return 'critical';
        } elseif ($currentStock <= $reorderLevel * 0.5) {
            return 'high';
        } else {
            return 'medium';
        }
    }
}