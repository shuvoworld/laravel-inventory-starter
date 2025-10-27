<?php

namespace App\Modules\PurchaseOrderItem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;
use App\Modules\Products\Models\Product;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use App\Traits\BelongsToStore;

class PurchaseOrderItem extends Model implements AuditableContract
{
    use HasFactory, Auditable, BelongsToStore;

    protected $table = 'purchase_order_items';

    protected $fillable = [
        'store_id',
        'purchase_order_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $auditInclude = [
        'purchase_order_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(\App\Modules\Products\Models\ProductVariant::class, 'variant_id');
    }

    /**
     * Get the full display name including variant information
     */
    public function getDisplayName(): string
    {
        $name = $this->product->name ?? 'Unknown Product';

        if ($this->variant_id && $this->variant) {
            $name .= ' (' . $this->variant->variant_name . ')';
        }

        return $name;
    }

    /**
     * Get the effective SKU (variant SKU if available, otherwise product SKU)
     */
    public function getEffectiveSku(): string
    {
        return $this->variant?->sku ?? $this->product?->sku ?? 'N/A';
    }

    /**
     * Get the effective cost price (variant cost if available, otherwise product cost)
     */
    public function getEffectiveCostPrice(): float
    {
        return $this->variant?->cost_price ?? $this->product?->cost_price ?? 0;
    }
}
