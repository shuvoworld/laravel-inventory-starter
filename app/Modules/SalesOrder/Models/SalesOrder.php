<?php

namespace App\Modules\SalesOrder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class SalesOrder extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'sales_orders';

    protected $fillable = [
        'order_number', 'customer_id', 'order_date', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'notes'
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected $auditInclude = [
        'order_number', 'customer_id', 'order_date', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'notes'
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Modules\Customers\Models\Customer::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Modules\SalesOrderItem\Models\SalesOrderItem::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(\App\Modules\StockMovement\Models\StockMovement::class, 'reference');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($salesOrder) {
            if (!$salesOrder->order_number) {
                $salesOrder->order_number = 'SO-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
