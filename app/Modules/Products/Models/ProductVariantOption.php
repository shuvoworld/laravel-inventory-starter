<?php

namespace App\Modules\Products\Models;

use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariantOption extends Model
{
    use BelongsToStore;

    protected $fillable = [
        'store_id',
        'name',
        'display_order'
    ];

    protected $casts = [
        'display_order' => 'integer'
    ];

    /**
     * Get all values for this option
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductVariantOptionValue::class, 'option_id')
            ->orderBy('display_order');
    }

    /**
     * Scope to get options with their values
     */
    public function scopeWithValues($query)
    {
        return $query->with('values');
    }
}
