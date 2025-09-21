<?php

namespace App\Modules\SalesReturn\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class SalesReturn extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'sales_returns';

    protected $fillable = [
        'return_number', 'sales_order_id', 'customer_id', 'return_date', 'status',
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
        'return_number', 'sales_order_id', 'customer_id', 'return_date', 'status',
        'reason', 'total_amount', 'notes'
    ];

    public function salesOrder()
    {
        return $this->belongsTo(\App\Modules\SalesOrder\Models\SalesOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Modules\Customers\Models\Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(\App\Modules\StockMovement\Models\StockMovement::class, 'reference');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($salesReturn) {
            if (!$salesReturn->return_number) {
                $salesReturn->return_number = 'SR-' . date('Y') . '-' . str_pad(static::whereYear('created_at', date('Y'))->count() + 1, 6, '0', STR_PAD_LEFT);
            }
        });
    }
}