<?php

namespace App\Modules\PurchaseOrder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use App\Modules\Suppliers\Models\Supplier;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use App\Traits\BelongsToStore;

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
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
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
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
