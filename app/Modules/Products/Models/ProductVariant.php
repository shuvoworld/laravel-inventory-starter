<?php

namespace App\Modules\Products\Models;

use App\Traits\BelongsToStore;
use App\Modules\SalesOrderItem\Models\SalesOrderItem;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\StockMovement\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ProductVariant extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'product_id',
        'sku',
        'variant_name',
        'barcode',
        'cost_price',
        'minimum_profit_margin',
        'standard_profit_margin',
        'floor_price',
        'target_price',
        'quantity_on_hand',
        'reorder_level',
        'weight',
        'image',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'minimum_profit_margin' => 'decimal:2',
        'standard_profit_margin' => 'decimal:2',
        'floor_price' => 'decimal:2',
        'target_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'quantity_on_hand' => 'integer',
        'reorder_level' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    protected $appends = ['image_url'];

    /**
     * Boot the model and set up event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically calculate floor_price and target_price when saving
        static::saving(function ($variant) {
            $variant->calculatePrices();

            // Auto-generate variant_name if not set
            if (empty($variant->variant_name)) {
                $variant->variant_name = $variant->generateVariantName();
            }
        });

        // Ensure only one default variant per product
        static::saving(function ($variant) {
            if ($variant->is_default) {
                static::where('product_id', $variant->product_id)
                    ->where('id', '!=', $variant->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Calculate floor_price and target_price based on cost_price and profit margins
     */
    public function calculatePrices()
    {
        $costPrice = $this->cost_price ?? $this->product?->cost_price;
        $minMargin = $this->minimum_profit_margin ?? $this->product?->minimum_profit_margin ?? 0;
        $stdMargin = $this->standard_profit_margin ?? $this->product?->standard_profit_margin ?? 0;

        if ($costPrice) {
            $this->floor_price = $costPrice + ($costPrice * ($minMargin / 100));
            $this->target_price = $costPrice + ($costPrice * ($stdMargin / 100));
        }
    }

    /**
     * Get the parent product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all option values for this variant
     */
    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariantOptionValue::class,
            'product_variant_attribute_values',
            'variant_id',
            'option_value_id'
        )->with('option');
    }

    /**
     * Get sales order items for this variant
     */
    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class, 'variant_id');
    }

    /**
     * Get purchase order items for this variant
     */
    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'variant_id');
    }

    /**
     * Get stock movements for this variant
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'variant_id');
    }

    /**
     * Generate variant name from option values
     */
    public function generateVariantName(): string
    {
        $values = $this->optionValues()
            ->with('option')
            ->get()
            ->sortBy('option.display_order')
            ->pluck('value')
            ->filter()
            ->join(' / ');

        return $values ?: 'Default';
    }

    /**
     * Get effective cost price (variant or parent)
     */
    public function getEffectiveCostPrice(): ?float
    {
        return $this->cost_price ?? $this->product?->cost_price;
    }

    /**
     * Get effective floor price (variant or parent)
     */
    public function getEffectiveFloorPrice(): ?float
    {
        return $this->floor_price ?? $this->product?->floor_price;
    }

    /**
     * Get effective target price (variant or parent)
     */
    public function getEffectiveTargetPrice(): ?float
    {
        return $this->target_price ?? $this->product?->target_price;
    }

    /**
     * Get effective minimum profit margin (variant or parent)
     */
    public function getEffectiveMinimumProfitMargin(): ?float
    {
        return $this->minimum_profit_margin ?? $this->product?->minimum_profit_margin;
    }

    /**
     * Get effective standard profit margin (variant or parent)
     */
    public function getEffectiveStandardProfitMargin(): ?float
    {
        return $this->standard_profit_margin ?? $this->product?->standard_profit_margin;
    }

    /**
     * Check if variant is low on stock
     */
    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_level;
    }

    /**
     * Get full SKU (parent SKU + variant SKU if exists)
     */
    public function getFullSkuAttribute(): string
    {
        if ($this->sku) {
            return $this->sku;
        }

        $parentSku = $this->product?->sku ?? '';
        $variantSuffix = $this->id ? "-V{$this->id}" : '';

        return $parentSku . $variantSuffix;
    }

    /**
     * Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return Storage::disk('public')->url($this->image);
        }

        // Fall back to parent product image
        return $this->product?->image_url;
    }

    /**
     * Scope to get only active variants
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get variants with low stock
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('quantity_on_hand', '<=', 'reorder_level');
    }

    /**
     * Scope to get variants for a specific product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }
}
