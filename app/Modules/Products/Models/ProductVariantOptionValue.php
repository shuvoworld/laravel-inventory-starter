<?php

namespace App\Modules\Products\Models;

use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductVariantOptionValue extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'option_id',
        'value',
        'display_order'
    ];

    protected $casts = [
        'display_order' => 'integer'
    ];

    /**
     * Get the option this value belongs to
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductVariantOption::class, 'option_id');
    }

    /**
     * Get all variants that use this option value
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_attribute_values',
            'option_value_id',
            'variant_id'
        );
    }

    /**
     * Get formatted display name (Option: Value)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->option->name . ': ' . $this->value;
    }
}
