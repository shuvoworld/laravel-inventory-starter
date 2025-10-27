<?php

namespace App\Modules\PurchaseOrder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\Suppliers\Models\Supplier;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use App\Traits\BelongsToStore;
use App\Services\WeightedAverageCostService;
use App\Services\AccountingService;
use Carbon\Carbon;

class PurchaseOrder extends Model implements AuditableContract
{
    use HasFactory, Auditable, BelongsToStore;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'store_id',
        'po_number',
        'supplier_id',
        'supplier_name',
        'order_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $auditInclude = [
        'po_number',
        'supplier_name',
        'order_date',
        'status',
        'total_amount',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseOrder) {
            if (!$purchaseOrder->po_number) {
                // Get count for current year and store
                $year = date('Y');
                $count = static::withoutGlobalScopes()
                    ->where('store_id', $purchaseOrder->store_id)
                    ->whereYear('created_at', $year)
                    ->count();

                $purchaseOrder->po_number = 'PO-' . $year . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
            }
        });

        static::updated(function ($purchaseOrder) {
            // Update Weighted Average Cost when purchase order status changes to 'received'
            if ($purchaseOrder->wasChanged('status') && in_array($purchaseOrder->status, ['received', 'confirmed'])) {
                $purchaseOrder->updateWeightedAverageCosts();

                // Post purchase order to accounting when received
                if ($purchaseOrder->status === 'received') {
                    try {
                        AccountingService::postPurchaseOrder($purchaseOrder);
                    } catch (\Exception $e) {
                        \Log::error('Error posting purchase order to accounting: ' . $e->getMessage());
                        // Continue even if accounting posting fails
                    }
                }
            }
        });
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // public function payments()
    // {
    //     return $this->hasMany(\App\Models\SupplierPayment::class);
    // }

    /**
     * Get the due amount for this purchase order
     */
    public function getDueAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Update payment status based on paid amount
     */
    public function updatePaymentStatus()
    {
        // Payment tracking is now handled directly in the purchase_orders table
        // $totalPaid = $this->payments()->sum('amount');

        // $this->paid_amount = $totalPaid;

        if ($this->paid_amount == 0) {
            $this->payment_status = 'unpaid';
        } elseif ($this->paid_amount >= $this->total_amount) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        $this->save();
    }

    /**
     * Check if purchase order is fully paid
     */
    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Update Weighted Average Costs for all products/variants in this purchase order
     */
    public function updateWeightedAverageCosts()
    {
        foreach ($this->items as $item) {
            if ($item->variant_id) {
                // Update variant-specific WAC
                WeightedAverageCostService::updateWACAfterPurchase($item->product_id, $item->variant_id);
            } else {
                // Update product WAC
                WeightedAverageCostService::updateWACAfterPurchase($item->product_id);
            }
        }
    }

    /**
     * Get WAC analysis for products/variants in this purchase order
     */
    public function getWACAnalysis()
    {
        $analysis = [];

        foreach ($this->items as $item) {
            $product = $item->product;
            if ($product) {
                $displayName = $item->getDisplayName();
                $sku = $item->getEffectiveSku();

                if ($item->variant_id) {
                    // Variant-specific WAC calculation
                    $currentWAC = WeightedAverageCostService::calculateWeightedAverageCost($item->product_id, $item->variant_id);
                    $previousWAC = $this->getPreviousWAC($item->product_id, $item->variant_id);
                } else {
                    // Product WAC calculation
                    $currentWAC = WeightedAverageCostService::calculateWeightedAverageCost($item->product_id);
                    $previousWAC = $this->getPreviousWAC($item->product_id);
                }

                $analysis[] = [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'display_name' => $displayName,
                    'sku' => $sku,
                    'quantity' => $item->quantity,
                    'unit_cost' => $item->unit_price,
                    'total_cost' => $item->total_price,
                    'previous_wac' => $previousWAC,
                    'new_wac' => $currentWAC,
                    'wac_change' => $currentWAC - $previousWAC,
                    'wac_change_percentage' => $previousWAC > 0 ? (($currentWAC - $previousWAC) / $previousWAC) * 100 : 0
                ];
            }
        }

        return $analysis;
    }

    /**
     * Get WAC before this purchase order
     */
    private function getPreviousWAC(int $productId, ?int $variantId = null): float
    {
        return WeightedAverageCostService::getWACForDateRange(
            $productId,
            Carbon::parse('1900-01-01'),
            $this->order_date->copy()->subDay(),
            $variantId
        );
    }

    /**
     * Check if purchase order has partial payment
     */
    public function isPartiallyPaid()
    {
        return $this->payment_status === 'partial';
    }

    /**
     * Check if purchase order is unpaid
     */
    public function isUnpaid()
    {
        return $this->payment_status === 'unpaid';
    }
}
