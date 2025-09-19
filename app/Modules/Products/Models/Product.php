<?php

namespace App\Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'sku', 'name', 'unit', 'price', 'quantity_on_hand', 'reorder_level',
    ];
}
