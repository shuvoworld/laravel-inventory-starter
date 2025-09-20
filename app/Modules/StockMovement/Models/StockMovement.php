<?php

namespace App\Modules\StockMovement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class StockMovement extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id', 'type', 'quantity', 'reference_type', 'reference_id', 'notes'
    ];

    protected $auditInclude = [
        'product_id', 'type', 'quantity', 'reference_type', 'reference_id', 'notes'
    ];

    public function product()
    {
        return $this->belongsTo(\App\Modules\Products\Models\Product::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($movement) {
            $product = $movement->product;
            if ($movement->type === 'in') {
                $product->increment('quantity_on_hand', $movement->quantity);
            } elseif ($movement->type === 'out') {
                $product->decrement('quantity_on_hand', $movement->quantity);
            } elseif ($movement->type === 'adjustment') {
                $product->quantity_on_hand = $movement->quantity;
                $product->save();
            }
        });
    }
}
