<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'sku', 'name', 'unit', 'price', 'quantity_on_hand', 'reorder_level',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function salesOrderItems()
    {
        return $this->hasMany(\App\Modules\SalesOrderItem\Models\SalesOrderItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(\App\Modules\StockMovement\Models\StockMovement::class);
    }

    public function isLowStock()
    {
        return $this->quantity_on_hand <= $this->reorder_level;
    }

    public function getCurrentStock()
    {
        return $this->quantity_on_hand;
    }
}
