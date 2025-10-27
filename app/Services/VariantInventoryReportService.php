<?php

namespace App\Services;

use App\Modules\Products\Models\Product;
use App\Modules\Products\Models\ProductVariant;
use App\Modules\StockMovement\Models\StockMovement;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VariantInventoryReportService
{
    /**
     * Get comprehensive variant inventory report
     */
    public static function getVariantInventoryReport(?array $filters = []): array
    {
        $query = ProductVariant::with(['product', 'optionValues.option'])
            ->where('store_id', auth()->user()->currentStoreId());

        // Apply filters
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['low_stock_only']) && $filters['low_stock_only']) {
            $query->whereColumn('quantity_on_hand', '<=', 'reorder_level');
        }

        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->where('is_active', true);
        }

        $variants = $query->get();

        $report = [
            'summary' => [
                'total_variants' => $variants->count(),
                'active_variants' => $variants->where('is_active', true)->count(),
                'low_stock_variants' => $variants->where('quantity_on_hand', '<=', 'reorder_level')->count(),
                'out_of_stock_variants' => $variants->where('quantity_on_hand', '<=', 0)->count(),
                'total_stock_value' => $variants->sum(function($variant) {
                    return $variant->quantity_on_hand * $variant->getEffectiveCostPrice();
                })
            ],
            'variants' => $variants->map(function($variant) {
                return [
                    'id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'product_name' => $variant->product->name,
                    'variant_name' => $variant->variant_name,
                    'display_name' => $variant->product->name . ' (' . $variant->variant_name . ')',
                    'sku' => $variant->sku,
                    'barcode' => $variant->barcode,
                    'quantity_on_hand' => $variant->quantity_on_hand,
                    'reorder_level' => $variant->reorder_level,
                    'is_low_stock' => $variant->quantity_on_hand <= $variant->reorder_level,
                    'is_out_of_stock' => $variant->quantity_on_hand <= 0,
                    'cost_price' => $variant->getEffectiveCostPrice(),
                    'target_price' => $variant->getEffectiveTargetPrice(),
                    'stock_value' => $variant->quantity_on_hand * $variant->getEffectiveCostPrice(),
                    'potential_profit' => $variant->quantity_on_hand * ($variant->getEffectiveTargetPrice() - $variant->getEffectiveCostPrice()),
                    'is_active' => $variant->is_active,
                    'is_default' => $variant->is_default,
                    'weight' => $variant->weight,
                    'image' => $variant->image,
                    'options' => $variant->optionValues->map(function($optionValue) {
                        return [
                            'option' => $optionValue->option->name,
                            'value' => $optionValue->value
                        ];
                    })->toArray(),
                    'created_at' => $variant->created_at,
                    'updated_at' => $variant->updated_at
                ];
            })->toArray()
        ];

        return $report;
    }

    /**
     * Get variant stock movements report
     */
    public static function getVariantStockMovementsReport(?int $variantId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = StockMovement::with(['variant.product', 'variant.optionValues.option', 'user'])
            ->where('store_id', auth()->user()->currentStoreId())
            ->whereNotNull('variant_id');

        if ($variantId) {
            $query->where('variant_id', $variantId);
        }

        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        $movements = $query->latest()->get();

        return [
            'summary' => [
                'total_movements' => $movements->count(),
                'total_in' => $movements->where('movement_type', 'in')->sum('quantity'),
                'total_out' => $movements->where('movement_type', 'out')->sum('quantity'),
                'period_start' => $startDate?->toDateString(),
                'period_end' => $endDate?->toDateString()
            ],
            'movements' => $movements->map(function($movement) {
                return [
                    'id' => $movement->id,
                    'date' => $movement->created_at->toDateTimeString(),
                    'variant' => [
                        'id' => $movement->variant->id,
                        'name' => $movement->variant->variant_name,
                        'sku' => $movement->variant->sku,
                        'product_name' => $movement->variant->product->name
                    ],
                    'movement_type' => $movement->movement_type,
                    'transaction_type' => $movement->transaction_type,
                    'quantity' => $movement->quantity,
                    'reference_type' => $movement->reference_type,
                    'reference_id' => $movement->reference_id,
                    'notes' => $movement->notes,
                    'user' => $movement->user->name
                ];
            })->toArray()
        ];
    }

    /**
     * Get variant sales performance report
     */
    public static function getVariantSalesPerformanceReport(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = SalesOrderItem::with(['variant.product', 'variant.optionValues.option', 'salesOrder'])
            ->whereHas('salesOrder', function($q) {
                $q->where('store_id', auth()->user()->currentStoreId());
            })
            ->whereNotNull('variant_id');

        if ($startDate) {
            $query->whereHas('salesOrder', function($q) use ($startDate) {
                $q->where('order_date', '>=', $startDate);
            });
        }

        if ($endDate) {
            $query->whereHas('salesOrder', function($q) use ($endDate) {
                $q->where('order_date', '<=', $endDate);
            });
        }

        $items = $query->get();

        // Group by variant
        $variantStats = $items->groupBy('variant_id')->map(function($variantItems) {
            $firstItem = $variantItems->first();
            $variant = $firstItem->variant;

            return [
                'variant_id' => $variant->id,
                'variant_name' => $variant->variant_name,
                'product_name' => $variant->product->name,
                'display_name' => $variant->product->name . ' (' . $variant->variant_name . ')',
                'sku' => $variant->sku,
                'total_quantity_sold' => $variantItems->sum('quantity'),
                'total_revenue' => $variantItems->sum('final_price'),
                'total_cost' => $variantItems->sum('cogs_amount'),
                'total_profit' => $variantItems->sum('profit_amount'),
                'profit_margin' => $variantItems->sum('final_price') > 0
                    ? ($variantItems->sum('profit_amount') / $variantItems->sum('final_price')) * 100
                    : 0,
                'average_unit_price' => $variantItems->sum('quantity') > 0
                    ? $variantItems->sum('final_price') / $variantItems->sum('quantity')
                    : 0,
                'orders_count' => $variantItems->pluck('sales_order_id')->unique()->count(),
                'current_stock' => $variant->quantity_on_hand,
                'last_sold_date' => $variantItems->max('created_at')?->toDateString()
            ];
        })->sortByDesc('total_revenue')->values();

        return [
            'summary' => [
                'total_variants_sold' => $variantStats->count(),
                'total_revenue' => $variantStats->sum('total_revenue'),
                'total_profit' => $variantStats->sum('total_profit'),
                'average_profit_margin' => $variantStats->avg('profit_margin'),
                'period_start' => $startDate?->toDateString(),
                'period_end' => $endDate?->toDateString()
            ],
            'variants' => $variantStats->toArray()
        ];
    }

    /**
     * Get variant valuation report
     */
    public static function getVariantValuationReport(): array
    {
        $variants = ProductVariant::with(['product'])
            ->where('store_id', auth()->user()->currentStoreId())
            ->where('quantity_on_hand', '>', 0)
            ->get();

        $valuationByProduct = $variants->groupBy('product_id')->map(function($productVariants) {
            $product = $productVariants->first()->product;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variants_count' => $productVariants->count(),
                'total_quantity' => $productVariants->sum('quantity_on_hand'),
                'total_cost_value' => $productVariants->sum(function($variant) {
                    return $variant->quantity_on_hand * $variant->getEffectiveCostPrice();
                }),
                'total_retail_value' => $productVariants->sum(function($variant) {
                    return $variant->quantity_on_hand * $variant->getEffectiveTargetPrice();
                }),
                'potential_profit' => $productVariants->sum(function($variant) {
                    return $variant->quantity_on_hand * ($variant->getEffectiveTargetPrice() - $variant->getEffectiveCostPrice());
                })
            ];
        })->sortByDesc('total_cost_value');

        return [
            'summary' => [
                'total_products_with_stock' => $valuationByProduct->count(),
                'total_variants_with_stock' => $variants->count(),
                'total_quantity' => $variants->sum('quantity_on_hand'),
                'total_cost_value' => $variants->sum(function($variant) {
                    return $variant->quantity_on_hand * $variant->getEffectiveCostPrice();
                }),
                'total_retail_value' => $variants->sum(function($variant) {
                    return $variant->quantity_on_hand * $variant->getEffectiveTargetPrice();
                }),
                'total_potential_profit' => $variants->sum(function($variant) {
                    return $variant->quantity_on_hand * ($variant->getEffectiveTargetPrice() - $variant->getEffectiveCostPrice());
                })
            ],
            'by_product' => $valuationByProduct->toArray(),
            'by_variant' => $variants->map(function($variant) {
                return [
                    'variant_id' => $variant->id,
                    'variant_name' => $variant->variant_name,
                    'product_name' => $variant->product->name,
                    'sku' => $variant->sku,
                    'quantity' => $variant->quantity_on_hand,
                    'cost_value' => $variant->quantity_on_hand * $variant->getEffectiveCostPrice(),
                    'retail_value' => $variant->quantity_on_hand * $variant->getEffectiveTargetPrice(),
                    'potential_profit' => $variant->quantity_on_hand * ($variant->getEffectiveTargetPrice() - $variant->getEffectiveCostPrice())
                ];
            })->sortByDesc('cost_value')->toArray()
        ];
    }
}