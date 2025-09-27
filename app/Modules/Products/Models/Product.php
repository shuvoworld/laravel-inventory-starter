<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'sku', 'name', 'unit', 'price', 'cost_price', 'profit_margin', 'quantity_on_hand', 'reorder_level',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
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

    public function getProfitMargin()
    {
        if ($this->price > 0) {
            return (($this->price - $this->cost_price) / $this->price) * 100;
        }
        return 0;
    }

    public function calculateProfitMargin()
    {
        if ($this->price > 0) {
            $this->profit_margin = $this->getProfitMargin();
            return $this->save();
        }
        return false;
    }
}
