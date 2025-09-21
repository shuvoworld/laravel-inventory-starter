<?php

namespace App\Modules\SalesReturn\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class SalesReturnItem extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'sales_return_items';

    protected $fillable = [
        'sales_return_id', 'sales_order_item_id', 'product_id',
        'quantity_returned', 'unit_price', 'total_price', 'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $auditInclude = [
        'sales_return_id', 'sales_order_item_id', 'product_id',
        'quantity_returned', 'unit_price', 'total_price'
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(\App\Modules\SalesOrderItem\Models\SalesOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Modules\Products\Models\Product::class);
    }
}