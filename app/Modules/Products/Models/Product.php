<?php

namespace App\Modules\Products\Models;

use App\Traits\BelongsToStore;
use App\Services\StockCalculationService;
use App\Services\WeightedAverageCostService;
use App\Services\SalesPriceAnalysisService;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class Product extends Model
{
    use BelongsToStore;

    protected $table = 'products';

    protected $fillable = [
        'store_id', 'sku', 'name', 'image', 'brand_id', 'unit', 'cost_price', 'quantity_on_hand', 'reorder_level',
        'minimum_profit_margin', 'standard_profit_margin', 'floor_price', 'target_price',
        'has_variants', 'variant_type',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'minimum_profit_margin' => 'decimal:2',
        'standard_profit_margin' => 'decimal:2',
        'floor_price' => 'decimal:2',
        'target_price' => 'decimal:2',
        'has_variants' => 'boolean',
    ];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate floor_price and target_price when saving
        static::saving(function ($product) {
            $product->calculatePrices();
        });
    }

    /**
     * Calculate floor_price and target_price based on cost_price and profit margins
     * Logic: Floor Price = Product Cost + (Product Cost × Minimum Profit Margin %)
     * Logic: Target Price = Product Cost + (Product Cost × Standard Profit Margin %)
     */
    public function calculatePrices()
    {
        if ($this->cost_price) {
            // Floor Price = Cost Price + (Cost Price × Minimum Profit Margin %)
            $this->floor_price = $this->cost_price + ($this->cost_price * ($this->minimum_profit_margin / 100));

            // Target Price = Cost Price + (Cost Price × Standard Profit Margin %)
            $this->target_price = $this->cost_price + ($this->cost_price * ($this->standard_profit_margin / 100));
        }
    }

    public function salesOrderItems()
    {
        return $this->hasMany(\App\Modules\SalesOrderItem\Models\SalesOrderItem::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(\App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(\App\Modules\StockMovement\Models\StockMovement::class);
    }

    public function brand()
    {
        return $this->belongsTo(\App\Modules\Brand\Models\Brand::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(\App\Modules\ProductAttribute\Models\AttributeValue::class, 'product_attribute_values', 'product_id', 'attribute_value_id')->withTimestamps();
    }

    /**
     * Get all variants for this product
     */
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get only active variants for this product
     */
    public function activeVariants()
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    /**
     * Get the default variant for this product
     */
    public function defaultVariant()
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    /**
     * Get total stock across all variants or product stock
     */
    public function getTotalStock(): int
    {
        if ($this->has_variants) {
            return $this->variants()->sum('quantity_on_hand');
        }
        return $this->quantity_on_hand;
    }

    /**
     * Get total stock value across all variants using target prices
     */
    public function getTotalStockValue(): float
    {
        if ($this->has_variants) {
            $total = 0;
            foreach ($this->variants as $variant) {
                $price = $variant->getEffectiveTargetPrice() ?? 0;
                $total += $price * $variant->quantity_on_hand;
            }
            return $total;
        }
        return ($this->target_price ?? 0) * $this->quantity_on_hand;
    }

    /**
     * Determine if the product is low on stock based on its reorder level using movements-based calculation.
     */
    public function isLowStock(): bool
    {
        // For products with variants, check if any variant is low on stock
        if ($this->has_variants) {
            return $this->activeVariants()
                ->where('is_active', true)
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->exists();
        }

        // For non-variant products, use existing logic
        $currentStock = StockCalculationService::getStockForProduct($this->id);
        $reorderLevel = $this->reorder_level ?? 10;
        return $currentStock <= $reorderLevel;
    }

    /**
     * Get current stock using movements-based calculation (source of truth)
     */
    public function getCurrentStock()
    {
        return StockCalculationService::getStockForProduct($this->id);
    }

    /**
     * Get current stock alias for compatibility
     */
    public function getCurrentStockFromMovements(): int
    {
        return StockCalculationService::getStockForProduct($this->id);
    }

    /**
     * Get Weighted Average Cost (COGS) for this product
     */
    public function getWeightedAverageCost(): float
    {
        return WeightedAverageCostService::calculateWeightedAverageCost($this->id);
    }

    /**
     * Get current COGS (Cost of Goods Sold) using Weighted Average Cost
     */
    public function getCOGS(): float
    {
        return $this->getWeightedAverageCost();
    }

    /**
     * Calculate profit margin using Weighted Average Cost
     */
    public function getProfitMarginUsingWAC(): float
    {
        $wac = $this->getWeightedAverageCost();

        if ($this->price > 0) {
            return (($this->price - $wac) / $this->price) * 100;
        }

        return 0;
    }

    /**
     * Get total inventory value using Weighted Average Cost
     */
    public function getInventoryValueAtWAC(): float
    {
        $currentStock = StockCalculationService::getStockForProduct($this->id);
        $wac = $this->getWeightedAverageCost();

        return $currentStock * $wac;
    }

    /**
     * Get potential profit using Weighted Average Cost
     */
    public function getPotentialProfitUsingWAC(): float
    {
        $currentStock = StockCalculationService::getStockForProduct($this->id);
        $wac = $this->getWeightedAverageCost();

        return $currentStock * ($this->price - $wac);
    }

    /**
     * Check if Weighted Average Cost differs significantly from cost_price
     */
    public function hasWACDifference(float $threshold = 10.0): bool
    {
        $wac = $this->getWeightedAverageCost();
        $currentCostPrice = $this->cost_price ?? 0;

        if ($currentCostPrice == 0) {
            return false;
        }

        $difference = abs($wac - $currentCostPrice);
        $percentageDifference = ($difference / $currentCostPrice) * 100;

        return $percentageDifference > $threshold;
    }

    /**
     * Get WAC vs cost price difference percentage
     */
    public function getWACDifferencePercentage(): float
    {
        $wac = $this->getWeightedAverageCost();
        $currentCostPrice = $this->cost_price ?? 0;

        if ($currentCostPrice == 0) {
            return 0;
        }

        $difference = abs($wac - $currentCostPrice);
        return ($difference / $currentCostPrice) * 100;
    }

    /**
     * Get sales price statistics for this product
     */
    public function getSalesPriceStats(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        return SalesPriceAnalysisService::getProductSalesPriceStats($this->id, $startDate, $endDate);
    }

    /**
     * Get actual sales price history for this product
     */
    public function getSalesHistory(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        return SalesPriceAnalysisService::getProductSalesHistory($this->id, $startDate, $endDate);
    }

    /**
     * Get average actual selling price for this product
     */
    public function getAverageSellingPrice(?Carbon $startDate = null, ?Carbon $endDate = null): float
    {
        $stats = $this->getSalesPriceStats($startDate, $endDate);
        return $stats['average_price_per_unit'];
    }

    /**
     * Get price range (min-max) for this product
     */
    public function getPriceRange(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $stats = $this->getSalesPriceStats($startDate, $endDate);
        return [
            'minimum' => $stats['minimum_unit_price'],
            'maximum' => $stats['maximum_unit_price'],
            'range' => $stats['price_variance']
        ];
    }

    /**
     * Check if pricing is consistent (low variance)
     */
    public function hasConsistentPricing(float $thresholdPercentage = 10.0): bool
    {
        $stats = $this->getSalesPriceStats();

        if ($stats['total_transactions'] === 0 || $stats['average_unit_price'] === 0) {
            return true; // No data to determine inconsistency
        }

        $variancePercentage = ($stats['price_variance'] / $stats['average_unit_price']) * 100;
        return $variancePercentage <= $thresholdPercentage;
    }

    /**
     * Get pricing compliance status
     */
    public function getPricingCompliance(): array
    {
        $stats = $this->getSalesPriceStats();
        $basePrice = $this->price;
        $averageActualPrice = $stats['average_price_per_unit'];

        if ($stats['total_transactions'] === 0) {
            return [
                'status' => 'No Sales Data',
                'base_price' => $basePrice,
                'average_actual_price' => 0,
                'difference_percentage' => 0,
                'recommendation' => 'No sales history available to analyze pricing compliance.'
            ];
        }

        $priceDifference = $averageActualPrice - $basePrice;
        $priceDifferencePercentage = $basePrice > 0 ? ($priceDifference / $basePrice) * 100 : 0;

        $status = 'Excellent';
        if (abs($priceDifferencePercentage) > 20) {
            $status = 'Poor';
        } elseif (abs($priceDifferencePercentage) > 10) {
            $status = 'Fair';
        } elseif (abs($priceDifferencePercentage) > 5) {
            $status = 'Good';
        }

        return [
            'status' => $status,
            'base_price' => $basePrice,
            'average_actual_price' => $averageActualPrice,
            'price_difference' => $priceDifference,
            'difference_percentage' => $priceDifferencePercentage,
            'total_transactions' => $stats['total_transactions'],
            'recommendation' => $this->getPricingRecommendationText($priceDifferencePercentage)
        ];
    }

    /**
     * Get pricing recommendations
     */
    public function getPricingRecommendations(): array
    {
        return SalesPriceAnalysisService::getPricingRecommendations($this->id);
    }

    /**
     * Get real profit margin using actual selling prices (after discounts) and WAC
     */
    public function getActualProfitMargin(): float
    {
        $stats = $this->getSalesPriceStats();

        if ($stats['total_transactions'] === 0) {
            return 0;
        }

        $averageSellingPrice = $stats['average_price_per_unit']; // This now uses final_price/quantity
        $wac = $this->getWeightedAverageCost();

        if ($averageSellingPrice > 0) {
            return (($averageSellingPrice - $wac) / $averageSellingPrice) * 100;
        }

        return 0;
    }

    /**
     * Get recommendation text based on price difference
     */
    private function getPricingRecommendationText(float $percentageDifference): string
    {
        if (abs($percentageDifference) < 5) {
            return 'Pricing is well-aligned with actual sales. Continue current strategy.';
        }

        if ($percentageDifference > 15) {
            return 'Actual selling prices are significantly higher than base price. Consider increasing base price to capture additional margin.';
        }

        if ($percentageDifference < -15) {
            return 'Actual selling prices are significantly lower than base price. Review discount policies and market positioning.';
        }

        if ($percentageDifference > 0) {
            return 'Actual selling prices are higher than base price. Consider modest base price increase.';
        }

        return 'Actual selling prices are lower than base price. Review pricing strategy and competitive positioning.';
    }

    /**
     * Scope: only products that are low on stock.
     * Note: This scope uses the system quantity_on_hand for database filtering.
     * For accurate movements-based calculation, use the services directly.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_on_hand', '<=', 'reorder_level');
    }

    /**
     * Get products that are actually low on stock using movements-based calculation
     */
    public static function getLowStockProducts()
    {
        return Product::where('store_id', auth()->user()->store_id)
            ->get()
            ->filter(function ($product) {
                $currentStock = StockCalculationService::getStockForProduct($product->id);
                $reorderLevel = $product->reorder_level ?? 10;
                return $currentStock <= $reorderLevel;
            });
    }

    public function getProfitMargin()
    {
        if ($this->price > 0) {
            return (($this->price - $this->cost_price) / $this->price) * 100;
        }

        return 0;
    }

    public function calculateProfitMargin()
    {
        if ($this->price > 0) {
            $this->profit_margin = $this->getProfitMargin();

            return $this->save();
        }

        return false;
    }

    /**
     * Get the full URL for the product image.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return null;
        }

        // If it's already a full URL (http/https), return as is
        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        // Otherwise, prepend storage path
        return asset('storage/' . $this->image);
    }

    /**
     * Get a placeholder image URL if no image is set.
     */
    public function getImageOrPlaceholder(): string
    {
        return $this->image_url ?? asset('images/product-placeholder.svg');
    }

    /**
     * Delete the product image file.
     */
    public function deleteImage(): bool
    {
        if ($this->image && \Storage::disk('public')->exists($this->image)) {
            return \Storage::disk('public')->delete($this->image);
        }

        return false;
    }
}
