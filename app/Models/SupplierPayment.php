<?php

namespace App\Models;

use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Suppliers\Models\Supplier;
use App\Modules\PurchaseOrder\Models\PurchaseOrder;

class SupplierPayment extends Model
{
    use HasFactory, BelongsToStore;

    protected $fillable = [
        'store_id',
        'supplier_id',
        'purchase_order_id',
        'payment_number',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (!$payment->payment_number) {
                $year = date('Y');
                $count = static::withoutGlobalScopes()
                    ->where('store_id', $payment->store_id)
                    ->whereYear('created_at', $year)
                    ->count();

                $payment->payment_number = 'SP-' . $year . '-' . str_pad($count + 1, 6, '0', STR_PAD_LEFT);
            }
        });

        // Update purchase order payment status after payment is created
        static::created(function ($payment) {
            if ($payment->purchase_order_id) {
                $payment->purchaseOrder->updatePaymentStatus();
            }
        });

        // Update purchase order payment status after payment is deleted
        static::deleted(function ($payment) {
            if ($payment->purchase_order_id) {
                $payment->purchaseOrder->updatePaymentStatus();
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
