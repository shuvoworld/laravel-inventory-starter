<?php

namespace App\Modules\ProductAttribute\Models;

use App\Traits\BelongsToStore;
use App\Modules\AttributeSet\Models\AttributeSet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class ProductAttribute extends Model implements AuditableContract
{
    use HasFactory, Auditable, BelongsToStore;

    protected $table = 'product_attributes';

    protected $fillable = [
        'store_id',
        'attribute_set_id',
        'name',
    ];

    protected $auditInclude = [
        'store_id',
        'attribute_set_id',
        'name',
    ];

    /**
     * Get the attribute set that owns the attribute.
     */
    public function attributeSet()
    {
        return $this->belongsTo(AttributeSet::class);
    }

    /**
     * Get the values for this attribute.
     */
    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'product_attribute_id');
    }
}
