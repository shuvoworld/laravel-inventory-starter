<?php

namespace App\Modules\SalesOrderItem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use App\Traits\BelongsToStore;

class SalesOrderItem extends Model implements AuditableContract
{
    use HasFactory, Auditable, BelongsToStore;

    protected $table = 'sales_order_items';

    protected $fillable = [
        'store_id', 'sales_order_id', 'product_id', 'variant_id', 'quantity', 'unit_price', 'cost_price', 'total_price',
        'discount_amount', 'discount_type', 'discount_rate', 'final_price', 'discount_reason',
        'cogs_amount', 'profit_amount'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'final_price' => 'decimal:2',
        'cogs_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
    ];

    protected $auditInclude = [
        'sales_order_id', 'product_id', 'variant_id', 'quantity', 'unit_price', 'cost_price', 'total_price',
        'discount_amount', 'discount_type', 'discount_rate', 'final_price', 'discount_reason',
        'cogs_amount', 'profit_amount'
    ];

    public function salesOrder()
    {
        return $this->belongsTo(\App\Modules\SalesOrder\Models\SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Modules\Products\Models\Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(\App\Modules\Products\Models\ProductVariant::class, 'variant_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;

            // Set cost price from variant or product if not already set
            if (!$item->cost_price) {
                if ($item->variant_id && $item->variant) {
                    $item->cost_price = $item->variant->cost_price ?? $item->variant->product->cost_price ?? 0;
                } elseif ($item->product) {
                    $item->cost_price = $item->product->cost_price ?? 0;
                }
            }

            // Calculate COGS
            $item->cogs_amount = $item->quantity * $item->cost_price;

            // Calculate item discount
            $discount = 0;
            if ($item->discount_type === 'percentage' && $item->discount_rate) {
                $discount = $item->total_price * ($item->discount_rate / 100);
            } elseif ($item->discount_type === 'fixed') {
                $discount = $item->discount_rate;
            }

            $item->discount_amount = $discount;
            $item->final_price = $item->total_price - $discount;

            // Calculate profit amount
            $item->profit_amount = $item->final_price - $item->cogs_amount;
        });
    }

    public function getDiscountTypes()
    {
        return [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
            'none' => 'No Discount'
        ];
    }

    public function getProfitMargin()
    {
        if ($this->final_price > 0) {
            return ($this->profit_amount / $this->final_price) * 100;
        }
        return 0;
    }

    public function getMarkupPercentage()
    {
        if ($this->cost_price > 0) {
            return (($this->unit_price - $this->cost_price) / $this->cost_price) * 100;
        }
        return 0;
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
}
