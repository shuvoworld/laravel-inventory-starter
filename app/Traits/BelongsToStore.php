<?php

namespace App\Traits;

use App\Scopes\StoreScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToStore
{
    /**
     * Boot the belongs to store trait for a model.
     */
    protected static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope);

        // Automatically set store_id when creating
        static::creating(function (Model $model) {
            if (auth()->check() && !$model->store_id) {
                $model->store_id = auth()->user()->currentStoreId();
            }
        });
    }

    /**
     * Get the store that owns the model.
     */
    public function store()
    {
        return $this->belongsTo(\App\Modules\Stores\Models\Store::class);
    }
}
