<?php

namespace App\Modules\AttributeSet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStore;
use App\Modules\ProductAttribute\Models\ProductAttribute;

class AttributeSet extends Model
{
    use HasFactory, BelongsToStore;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the attributes for this set.
     */
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    /**
     * Scope for active attribute sets.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
