<?php

namespace App\Modules\ProductCategories\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStore;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use BelongsToStore;

    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'store_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug from name on creating
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);

                // Ensure slug is unique
                $originalSlug = $category->slug;
                $count = 1;
                while (static::where('slug', $category->slug)->exists()) {
                    $category->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });

        // Auto-generate slug from name on updating if name changed
        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);

                // Ensure slug is unique (excluding current record)
                $originalSlug = $category->slug;
                $count = 1;
                while (static::where('slug', $category->slug)->where('id', '!=', $category->id)->exists()) {
                    $category->slug = $originalSlug . '-' . $count;
                    $count++;
                }
            }
        });
    }

    /**
     * Get the products for this category.
     */
    public function products()
    {
        return $this->hasMany(\App\Modules\Products\Models\Product::class, 'category_id');
    }
}
