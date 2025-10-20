<?php

namespace App\Services;

use App\Modules\Products\Models\Product;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Services\StockCalculationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class WeightedAverageCostService
{
    /**
     * Calculate Weighted Average Cost for a product
     */
    public static function calculateWeightedAverageCost(int $productId): float
    {
        return Cache::remember(
            "product_wac_{$productId}",
            now()->addMinutes(30),
            function () use ($productId) {
                $result = PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                    ->where('purchase_order_items.product_id', $productId)
                    ->whereIn('purchase_orders.status', ['pending', 'confirmed', 'received'])
                    ->selectRaw("
                        SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_value,
                        SUM(purchase_order_items.quantity) as total_quantity
                    ")
                    ->first();

                if (!$result || $result->total_quantity == 0) {
                    // Fallback to product cost price if no purchase history
                    $product = Product::find($productId);
                    return $product ? ($product->cost_price ?? 0) : 0;
                }

                return $result->total_value / $result->total_quantity;
            }
        );
    }

    /**
     * Get Weighted Average Cost for multiple products at once
     */
    public static function calculateForAllProducts(): array
    {
        return Cache::remember(
            'all_products_wac',
            now()->addMinutes(15),
            function () {
                $results = PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                    ->whereIn('purchase_orders.status', ['pending', 'confirmed', 'received'])
                    ->selectRaw("
                        purchase_order_items.product_id,
                        SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_value,
                        SUM(purchase_order_items.quantity) as total_quantity
                    ")
                    ->groupBy('purchase_order_items.product_id')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $wac = $item->total_quantity > 0 ? $item->total_value / $item->total_quantity : 0;
                        return [
                            $item->product_id => [
                                'weighted_average_cost' => $wac,
                                'total_quantity_purchased' => $item->total_quantity,
                                'total_value' => $item->total_value,
                                'purchase_count' => PurchaseOrderItem::where('product_id', $item->product_id)
                                    ->join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
                                    ->whereIn('purchase_orders.status', ['confirmed', 'received'])
                                    ->count()
                            ]
                        ];
                    })
                    ->toArray();

                // Add products with no purchase history
                $productsWithoutPurchases = Product::whereDoesntHave('purchaseOrderItems')
                    ->orWhereHas('purchaseOrderItems', function ($query) {
                        $query->whereHas('purchaseOrder', function ($q) {
                            $q->whereIn('status', ['confirmed', 'received']);
                        });
                    }, '=', 0)
                    ->get()
                    ->mapWithKeys(function ($product) {
                        return [
                            $product->id => [
                                'weighted_average_cost' => $product->cost_price ?? 0,
                                'total_quantity_purchased' => 0,
                                'total_value' => 0,
                                'purchase_count' => 0
                            ]
                        ];
                    })
                    ->toArray();

                return array_merge($results, $productsWithoutPurchases);
            }
        );
    }

    /**
     * Get detailed purchase history for a product
     */
    public static function getPurchaseHistory(int $productId, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_order_items.product_id', $productId)
            ->whereIn('purchase_orders.status', ['pending', 'confirmed', 'received'])
            ->orderBy('purchase_orders.order_date', 'asc');

        if ($startDate) {
            $query->where('purchase_orders.order_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('purchase_orders.order_date', '<=', $endDate);
        }

        $purchases = $query->get([
            'purchase_orders.id as purchase_order_id',
            'purchase_orders.po_number',
            'purchase_orders.order_date',
            'purchase_order_items.quantity',
            'purchase_order_items.unit_price',
            'purchase_order_items.total_price'
        ]);

        $runningQuantity = 0;
        $runningValue = 0;
        $history = [];

        foreach ($purchases as $purchase) {
            $runningQuantity += $purchase->quantity;
            $runningValue += $purchase->total_price;
            $currentWAC = $runningQuantity > 0 ? $runningValue / $runningQuantity : 0;

            $history[] = [
                'purchase_order_id' => $purchase->purchase_order_id,
                'order_number' => $purchase->po_number,
                'order_date' => $purchase->order_date->format('Y-m-d'),
                'quantity' => $purchase->quantity,
                'unit_cost' => $purchase->unit_price, // Keep as unit_cost for consistency
                'total_cost' => $purchase->total_price, // Keep as total_cost for consistency
                'running_total_quantity' => $runningQuantity,
                'running_total_value' => $runningValue,
                'current_weighted_average_cost' => round($currentWAC, 4)
            ];
        }

        return $history;
    }

    /**
     * Update Weighted Average Cost after a new purchase
     */
    public static function updateWACAfterPurchase(int $productId): void
    {
        // Clear the cache for this product
        Cache::forget("product_wac_{$productId}");
        Cache::forget('all_products_wac');

        // Calculate new WAC
        $newWAC = self::calculateWeightedAverageCost($productId);

        // Update product cost_price with the new WAC
        self::syncProductCostPrice($productId, $newWAC);
    }

    /**
     * Sync product cost_price with calculated Average Cost (Total Cost / Total Quantity)
     */
    public static function syncProductCostPrice(int $productId, ?float $averageCost = null): void
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        // Calculate Average Cost if not provided
        if ($averageCost === null) {
            $averageCost = self::calculateAverageCost($productId);
        }

        // Update product cost_price
        $product->update(['cost_price' => $averageCost]);
    }

    /**
     * Calculate simple Average Cost = Total Cost / Total Quantity
     */
    public static function calculateAverageCost(int $productId): float
    {
        $result = \App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_order_items.product_id', $productId)
            ->whereIn('purchase_orders.status', ['confirmed', 'processing', 'received'])
            ->selectRaw("
                SUM(purchase_order_items.quantity) as total_quantity,
                SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_cost
            ")
            ->first();

        if (!$result || $result->total_quantity == 0) {
            // Fallback to product cost price if no purchase history
            $product = Product::find($productId);
            return $product ? ($product->cost_price ?? 0) : 0;
        }

        return $result->total_cost / $result->total_quantity;
    }

    /**
     * Sync cost_price for all products using Average Cost
     */
    public static function syncAllProductsCostPrice(): int
    {
        $products = Product::all();
        $updated = 0;

        foreach ($products as $product) {
            $averageCost = self::calculateAverageCost($product->id);
            self::syncProductCostPrice($product->id, $averageCost);
            $updated++;
        }

        return $updated;
    }

    /**
     * Get Weighted Average Cost analysis for reporting
     */
    public static function getWACAnalysis(): array
    {
        $allWAC = self::calculateForAllProducts();

        $analysis = [
            'total_products' => count($allWAC),
            'products_with_purchase_history' => 0,
            'products_without_purchase_history' => 0,
            'average_wac_value' => 0,
            'total_inventory_value_at_wac' => 0,
            'wac_distribution' => [
                'low_cost' => 0,    // <$10
                'medium_cost' => 0,  // $10-$100
                'high_cost' => 0,    // >$100
            ],
            'top_cost_products' => [],
            'wac_changes_needed' => []
        ];

        $totalWAC = 0;
        $totalInventoryValue = 0;

        foreach ($allWAC as $productId => $data) {
            $wac = $data['weighted_average_cost'];

            if ($data['purchase_count'] > 0) {
                $analysis['products_with_purchase_history']++;
                $totalWAC += $wac;
            } else {
                $analysis['products_without_purchase_history']++;
            }

            // Calculate inventory value at WAC
            $currentStock = StockCalculationService::getStockForProduct($productId);
            $inventoryValue = $currentStock * $wac;
            $totalInventoryValue += $inventoryValue;

            // Categorize by cost
            if ($wac < 10) {
                $analysis['wac_distribution']['low_cost']++;
            } elseif ($wac <= 100) {
                $analysis['wac_distribution']['medium_cost']++;
            } else {
                $analysis['wac_distribution']['high_cost']++;
            }

            // Collect high-value products for analysis
            if ($wac > 50) {
                $product = Product::find($productId);
                if ($product) {
                    $analysis['top_cost_products'][] = [
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'weighted_average_cost' => $wac,
                        'current_stock' => $currentStock,
                        'inventory_value_at_wac' => $inventoryValue,
                        'total_purchases' => $data['purchase_count']
                    ];
                }
            }

            // Check if WAC differs significantly from current cost_price
            $product = Product::find($productId);
            if ($product && $data['purchase_count'] > 0) {
                $currentCostPrice = $product->cost_price ?? 0;
                $difference = abs($wac - $currentCostPrice);
                $percentageDifference = $currentCostPrice > 0 ? ($difference / $currentCostPrice) * 100 : 0;

                if ($percentageDifference > 10) { // 10% threshold
                    $analysis['wac_changes_needed'][] = [
                        'product_id' => $productId,
                        'product_name' => $product->name,
                        'current_cost_price' => $currentCostPrice,
                        'weighted_average_cost' => $wac,
                        'difference' => $difference,
                        'percentage_difference' => round($percentageDifference, 2),
                        'recommendation' => $wac > $currentCostPrice ? 'Update cost price upward' : 'Consider cost price adjustment'
                    ];
                }
            }
        }

        $analysis['average_wac_value'] = $analysis['products_with_purchase_history'] > 0
            ? $totalWAC / $analysis['products_with_purchase_history']
            : 0;

        $analysis['total_inventory_value_at_wac'] = $totalInventoryValue;

        // Sort high-value products by WAC
        usort($analysis['top_cost_products'], fn($a, $b) => $b['weighted_average_cost'] <=> $a['weighted_average_cost']);

        // Sort WAC changes by percentage difference
        usort($analysis['wac_changes_needed'], fn($a, $b) => $b['percentage_difference'] <=> $a['percentage_difference']);

        return $analysis;
    }

    /**
     * Get WAC for specific date range
     */
    public static function getWACForDateRange(int $productId, Carbon $startDate, Carbon $endDate): float
    {
        $result = PurchaseOrderItem::join('purchase_orders', 'purchase_order_items.purchase_order_id', '=', 'purchase_orders.id')
            ->where('purchase_order_items.product_id', $productId)
            ->whereIn('purchase_orders.status', ['pending', 'confirmed', 'received'])
            ->whereBetween('purchase_orders.order_date', [$startDate, $endDate])
            ->selectRaw("
                SUM(purchase_order_items.quantity * purchase_order_items.unit_price) as total_value,
                SUM(purchase_order_items.quantity) as total_quantity
            ")
            ->first();

        if (!$result || $result->total_quantity == 0) {
            return 0;
        }

        return $result->total_value / $result->total_quantity;
    }

    /**
     * Clear WAC cache for all products
     */
    public static function clearAllCache(): void
    {
        Cache::forget('all_products_wac');

        // Clear individual product caches (if needed for specific products)
        // This would typically be called after bulk updates
    }
}