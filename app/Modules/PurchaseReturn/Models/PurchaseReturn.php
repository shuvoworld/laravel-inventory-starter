<?php

namespace App\Modules\PurchaseReturn\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class PurchaseReturn extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'purchase_returns';

    protected $fillable = [
        'return_number', 'purchase_order_id', 'supplier_name', 'return_date', 'status',
        'reason', 'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'notes'
    ];

    protected $casts = [
        'return_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $auditInclude = [
        'return_number', 'purchase_order_id', 'supplier_name', 'return_date', 'status',
        'reason', 'total_amount', 'notes'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(\App\Modules\PurchaseOrder\Models\PurchaseOrder::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(\App\Modules\StockMovement\Models\StockMovement::class, 'reference');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchaseReturn) {
            if (!$purchaseReturn->return_number) {
                $purchaseReturn->return_number = 'PR-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}