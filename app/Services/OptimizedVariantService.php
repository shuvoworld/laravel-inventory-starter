<?php

namespace App\Services;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductVariant;
use App\Modules\Products\Models\ProductVariantOption;
use App\Modules\Products\Models\ProductVariantOptionValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OptimizedVariantService
{
    /**
     * Get products with variants optimized for POS display
     */
    public static function getProductsWithVariantsForPos(?int $categoryId = null, ?string $search = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Product::with([
            'variants' => function($query) {
                $query->with('optionValues.option')
                      ->select(['id', 'product_id', 'variant_name', 'sku', 'price', 'target_price', 'quantity_on_hand', 'image', 'is_active'])
                      ->where('is_active', true);
            }
        ])->select(['id', 'name', 'sku', 'price', 'target_price', 'has_variants', 'image']);

        if ($categoryId && $categoryId !== 'all') {
            $query->where('brand_id', $categoryId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        return $query->where('is_active', true)
                    ->orderBy('name')
                    ->get();
    }

    /**
     * Get variant details with all related data for display
     */
    public static function getVariantDetails(int $variantId): ?ProductVariant
    {
        return ProductVariant::with([
            'product:id,name,sku,price,target_price,image',
            'optionValues.option:id,name',
            'salesOrderItems' => function($query) {
                $query->select(['id', 'variant_id', 'quantity', 'final_price', 'created_at'])
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            },
            'purchaseOrderItems' => function($query) {
                $query->select(['id', 'variant_id', 'quantity', 'unit_price', 'created_at'])
                      ->orderBy('created_at', 'desc')
                      ->limit(10);
            }
        ])->find($variantId);
    }

    /**
     * Get variants with stock information for reports
     */
    public static function getVariantsWithStockData(?int $productId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = ProductVariant::with([
            'product:id,name,sku,brand_id',
            'product.brand:id,name',
            'optionValues.option:id,name'
        ])->select([
            'id', 'product_id', 'variant_name', 'sku', 'cost_price', 'target_price',
            'quantity_on_hand', 'reorder_level', 'is_active', 'is_default', 'created_at'
        ]);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->orderBy('product_id')
                    ->orderBy('variant_name')
                    ->get();
    }

    /**
     * Get variant sales data with optimized queries
     */
    public static function getVariantSalesData(\DateTime $startDate, \DateTime $endDate): \Illuminate\Support\Collection
    {
        return DB::table('sales_order_items as soi')
            ->join('sales_orders as so', 'soi.sales_order_id', '=', 'so.id')
            ->join('product_variants as pv', 'soi.variant_id', '=', 'pv.id')
            ->join('products as p', 'pv.product_id', '=', 'p.id')
            ->select([
                'pv.id as variant_id',
                'pv.variant_name',
                'pv.sku as variant_sku',
                'p.name as product_name',
                'p.sku as product_sku',
                DB::raw('SUM(soi.quantity) as total_quantity_sold'),
                DB::raw('SUM(soi.final_price) as total_revenue'),
                DB::raw('SUM(soi.cogs_amount) as total_cost'),
                DB::raw('SUM(soi.profit_amount) as total_profit'),
                DB::raw('COUNT(DISTINCT so.id) as orders_count'),
                DB::raw('MAX(so.order_date) as last_sold_date')
            ])
            ->where('so.order_date', '>=', $startDate)
            ->where('so.order_date', '<=', $endDate)
            ->whereNotNull('soi.variant_id')
            ->groupBy('pv.id', 'pv.variant_name', 'pv.sku', 'p.name', 'p.sku')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    /**
     * Get low stock variants with product information
     */
    public static function getLowStockVariants(): \Illuminate\Support\Collection
    {
        return ProductVariant::with([
            'product:id,name,sku',
            'optionValues.option:id,name'
        ])
        ->select([
            'id', 'product_id', 'variant_name', 'sku', 'quantity_on_hand',
            'reorder_level', 'cost_price', 'target_price'
        ])
        ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
        ->where('quantity_on_hand', '>', 0)
        ->orderByRaw('(quantity_on_hand / GREATEST(reorder_level, 1)) ASC')
        ->orderBy('quantity_on_hand', 'ASC')
        ->get();
    }

    /**
     * Get out of stock variants
     */
    public static function getOutOfStockVariants(): \Illuminate\Support\Collection
    {
        return ProductVariant::with([
            'product:id,name,sku',
            'optionValues.option:id,name'
        ])
        ->select([
            'id', 'product_id', 'variant_name', 'sku', 'quantity_on_hand',
            'cost_price', 'target_price', 'updated_at'
        ])
        ->where('quantity_on_hand', '<=', 0)
        ->where('is_active', true)
        ->orderBy('updated_at', 'desc')
        ->get();
    }

    /**
     * Get variant inventory valuation with cost calculation
     */
    public static function getVariantInventoryValuation(): array
    {
        $data = ProductVariant::with([
            'product:id,name,sku,brand_id',
            'product.brand:id,name'
        ])
        ->select([
            'id', 'product_id', 'variant_name', 'sku', 'quantity_on_hand',
            'cost_price', 'target_price', 'created_at'
        ])
        ->where('quantity_on_hand', '>', 0)
        ->get();

        $totalCostValue = 0;
        $totalRetailValue = 0;
        $totalPotentialProfit = 0;

        $variants = $data->map(function($variant) use (&$totalCostValue, &$totalRetailValue, &$totalPotentialProfit) {
            $costValue = $variant->quantity_on_hand * $variant->getEffectiveCostPrice();
            $retailValue = $variant->quantity_on_hand * $variant->getEffectiveTargetPrice();
            $potentialProfit = $retailValue - $costValue;

            $totalCostValue += $costValue;
            $totalRetailValue += $retailValue;
            $totalPotentialProfit += $potentialProfit;

            return [
                'id' => $variant->id,
                'variant_name' => $variant->variant_name,
                'product_name' => $variant->product->name,
                'sku' => $variant->sku,
                'quantity' => $variant->quantity_on_hand,
                'cost_price' => $variant->getEffectiveCostPrice(),
                'target_price' => $variant->getEffectiveTargetPrice(),
                'cost_value' => $costValue,
                'retail_value' => $retailValue,
                'potential_profit' => $potentialProfit
            ];
        });

        return [
            'summary' => [
                'total_variants_with_stock' => $variants->count(),
                'total_cost_value' => $totalCostValue,
                'total_retail_value' => $totalRetailValue,
                'total_potential_profit' => $totalPotentialProfit
            ],
            'variants' => $variants->sortByDesc('cost_value')->values()
        ];
    }

    /**
     * Search variants with optimized indexing
     */
    public static function searchVariants(string $query, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return ProductVariant::with([
            'product:id,name,sku',
            'optionValues.option:id,name'
        ])
        ->select([
            'id', 'product_id', 'variant_name', 'sku', 'price', 'target_price',
            'quantity_on_hand', 'is_active'
        ])
        ->where('is_active', true)
        ->where(function($q) use ($query) {
            $q->where('variant_name', 'LIKE', "%{$query}%")
              ->orWhere('sku', 'LIKE', "%{$query}%")
              ->orWhereHas('product', function($subQ) use ($query) {
                  $subQ->where('name', 'LIKE', "%{$query}%")
                        ->orWhere('sku', 'LIKE', "%{$query}%");
              });
        })
        ->limit($limit)
        ->get();
    }

    /**
     * Get variant options with values for bulk operations
     */
    public static function getVariantOptionsWithValues(): \Illuminate\Database\Eloquent\Collection
    {
        return ProductVariantOption::with([
            'values' => function($query) {
                $query->select(['id', 'option_id', 'value', 'display_order'])
                      ->orderBy('display_order');
            }
        ])
        ->select(['id', 'name', 'display_order'])
        ->orderBy('display_order')
        ->get();
    }

    /**
     * Update variant stock with validation and logging
     */
    public static function updateVariantStock(int $variantId, int $newQuantity, string $reason = '', ?int $userId = null): bool
    {
        return DB::transaction(function() use ($variantId, $newQuantity, $reason, $userId) {
            $variant = ProductVariant::lockForUpdate()->find($variantId);

            if (!$variant) {
                return false;
            }

            $oldQuantity = $variant->quantity_on_hand;
            $quantityDiff = $newQuantity - $oldQuantity;

            if ($quantityDiff == 0) {
                return true; // No change needed
            }

            $variant->quantity_on_hand = $newQuantity;
            $variant->save();

            // Record stock movement
            $movementType = $quantityDiff > 0 ? 'in' : 'out';
            $transactionType = 'manual_adjustment';

            StockMovementService::recordAdjustment(
                $variant->product_id,
                $variant->id,
                $movementType,
                abs($quantityDiff),
                $reason ?: "Manual stock adjustment: {$oldQuantity} → {$newQuantity}"
            );

            return true;
        });
    }

    /**
     * Get variant performance metrics
     */
    public static function getVariantPerformanceMetrics(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        $endDate = now();

        $salesData = self::getVariantSalesData($startDate, $endDate);

        return [
            'period_days' => $days,
            'total_variants_sold' => $salesData->count(),
            'top_performing_variants' => $salesData->take(10),
            'bottom_performing_variants' => $salesData->reverse()->take(10),
            'average_profit_margin' => $salesData->avg(function($item) {
                return $item->total_revenue > 0 ? ($item->total_profit / $item->total_revenue) * 100 : 0;
            }),
            'total_revenue' => $salesData->sum('total_revenue'),
            'total_profit' => $salesData->sum('total_profit')
        ];
    }
}