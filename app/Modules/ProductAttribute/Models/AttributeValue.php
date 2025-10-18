<?php

namespace App\Modules\ProductAttribute\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class AttributeValue extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'attribute_values';

    protected $fillable = [
        'product_attribute_id',
        'value',
    ];

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'product_attribute_id');
    }

    public function products()
    {
        return $this->belongsToMany(\App\Modules\Products\Models\Product::class, 'product_attribute_values', 'attribute_value_id', 'product_id')->withTimestamps();
    }
}
