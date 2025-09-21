<?php

namespace App\Modules\PurchaseReturn\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class PurchaseReturnItem extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'purchase_return_items';

    protected $fillable = [
        'purchase_return_id', 'purchase_order_item_id', 'product_id',
        'quantity_returned', 'unit_price', 'total_price', 'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    protected $auditInclude = [
        'purchase_return_id', 'purchase_order_item_id', 'product_id',
        'quantity_returned', 'unit_price', 'total_price'
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(\App\Modules\PurchaseOrderItem\Models\PurchaseOrderItem::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Modules\Products\Models\Product::class);
    }
}