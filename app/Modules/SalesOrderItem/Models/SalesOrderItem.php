<?php

namespace App\Modules\SalesOrderItem\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class SalesOrderItem extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'sales_order_items';

    protected $fillable = [
        'sales_order_id', 'product_id', 'quantity', 'unit_price', 'total_price'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $auditInclude = [
        'sales_order_id', 'product_id', 'quantity', 'unit_price', 'total_price'
    ];

    public function salesOrder()
    {
        return $this->belongsTo(\App\Modules\SalesOrder\Models\SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Modules\Products\Models\Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->total_price = $item->quantity * $item->unit_price;
        });
    }
}
