<?php

namespace App\Modules\Stores\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class Store extends Model
{
    use SoftDeletes;

    protected $table = 'stores';

    protected $fillable = [
        'name',
        'slug',
        'address',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users associated with this store
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if store is active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }
}