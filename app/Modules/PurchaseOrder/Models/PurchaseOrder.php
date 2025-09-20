<?php

namespace App\Modules\PurchaseOrder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class PurchaseOrder extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
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
                $purchaseOrder->po_number = 'PO-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
