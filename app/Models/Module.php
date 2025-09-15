<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'namespace',
        'title',
        'path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
        * Safe check for active status. Returns true if table missing (during first deploy) to avoid breaking app.
        */
    public static function isActive(string $name): bool
    {
        try {
            if (!DB::getSchemaBuilder()->hasTable('modules')) {
                return true; // table not ready yet, allow
            }
            return (bool) static::query()->where('name', $name)->where('is_active', true)->exists();
        } catch (\Throwable $e) {
            return true; // fail-open
        }
    }
}
