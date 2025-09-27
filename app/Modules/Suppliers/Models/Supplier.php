<?php

namespace App\Modules\Suppliers\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'contact_person',
        'tax_id',
        'payment_terms',
        'credit_limit',
        'status',
        'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2'
    ];

    /**
     * Relationship with Purchase Orders
     */
    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get total purchase amount for this supplier
     */
    public function getTotalPurchaseAmountAttribute()
    {
        return $this->purchaseOrders()->sum('total_amount');
    }

    /**
     * Get pending purchase orders count
     */
    public function getPendingOrdersCountAttribute()
    {
        return $this->purchaseOrders()->where('status', 'pending')->count();
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);

        return implode(', ', $parts);
    }

    /**
     * Scope for active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('contact_person', 'like', "%{$search}%");
        });
    }

    /**
     * Generate next supplier code
     */
    public static function generateCode()
    {
        $lastSupplier = self::orderBy('code', 'desc')->first();

        if (!$lastSupplier) {
            return 'SUP001';
        }

        $lastNumber = (int) substr($lastSupplier->code, 3);
        $nextNumber = $lastNumber + 1;

        return 'SUP' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method for automatic code generation
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($supplier) {
            if (!$supplier->code) {
                $supplier->code = self::generateCode();
            }
        });
    }
}