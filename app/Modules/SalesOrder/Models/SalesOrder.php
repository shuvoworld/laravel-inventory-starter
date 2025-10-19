<?php

namespace App\Modules\SalesOrder\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use App\Traits\BelongsToStore;

class SalesOrder extends Model implements AuditableContract
{
    use HasFactory, Auditable, BelongsToStore;

    protected $table = 'sales_orders';

    protected $fillable = [
        'store_id', 'order_number', 'customer_id', 'order_date', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'notes',
        'payment_method', 'payment_status', 'paid_amount', 'change_amount',
        'discount_type', 'discount_rate', 'discount_reason', 'payment_date',
        'reference_number', 'hold_reason', 'hold_date', 'release_date',
        'held_by', 'released_by', 'cogs_amount', 'profit_amount'
    ];

    protected $casts = [
        'order_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'discount_rate' => 'decimal:2',
        'payment_date' => 'datetime',
        'hold_date' => 'datetime',
        'release_date' => 'datetime',
        'cogs_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
    ];

    protected $auditInclude = [
        'order_number', 'customer_id', 'order_date', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'notes',
        'payment_method', 'payment_status', 'paid_amount', 'change_amount',
        'discount_type', 'discount_rate', 'discount_reason', 'cogs_amount', 'profit_amount'
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Modules\Customers\Models\Customer::class);
    }

    public function items()
    {
        return $this->hasMany(\App\Modules\SalesOrderItem\Models\SalesOrderItem::class);
    }

    public function stockMovements()
    {
        return $this->morphMany(\App\Modules\StockMovement\Models\StockMovement::class, 'reference');
    }

    public function heldBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'held_by');
    }

    public function releasedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'released_by');
    }

    public function getPaymentMethods()
    {
        return [
            'cash' => 'Cash',
            'card' => 'Card',
            'mobile_banking' => 'Mobile Banking',
            'bank_transfer' => 'Bank Transfer',
            'cheque' => 'Cheque'
        ];
    }

    public function getPaymentStatuses()
    {
        return [
            'pending' => 'Pending',
            'partial' => 'Partial',
            'paid' => 'Paid',
            'overpaid' => 'Overpaid',
            'refunded' => 'Refunded'
        ];
    }

    public function getDiscountTypes()
    {
        return [
            'fixed' => 'Fixed Amount',
            'percentage' => 'Percentage',
            'none' => 'No Discount'
        ];
    }

    public function calculateTotals()
    {
        $subtotal = $this->items()->sum('final_price');
        $cogs = $this->items()->sum('cogs_amount');
        $discount = 0;

        if ($this->discount_type === 'percentage' && $this->discount_rate) {
            $discount = $subtotal * ($this->discount_rate / 100);
        } elseif ($this->discount_type === 'fixed') {
            $discount = $this->discount_rate;
        }

        $totalAfterDiscount = $subtotal - $discount;
        $profit = $totalAfterDiscount - $cogs;

        $this->subtotal = $subtotal;
        $this->discount_amount = $discount;
        $this->total_amount = $totalAfterDiscount;
        $this->cogs_amount = $cogs;
        $this->profit_amount = $profit;

        return $this;
    }

    public function getGrossProfit()
    {
        return $this->total_amount - $this->cogs_amount;
    }

    public function getGrossProfitMargin()
    {
        if ($this->total_amount > 0) {
            return ($this->getGrossProfit() / $this->total_amount) * 100;
        }
        return 0;
    }

    public function getNetProfit()
    {
        return $this->getGrossProfit(); // Can be extended to include operating expenses
    }

    public function getNetProfitMargin()
    {
        if ($this->total_amount > 0) {
            return ($this->getNetProfit() / $this->total_amount) * 100;
        }
        return 0;
    }

      public static function create(array $attributes = [])
    {
        // Ensure store_id is set before generating order number
        if (!isset($attributes['store_id']) && auth()->check()) {
            $attributes['store_id'] = auth()->user()->currentStoreId();
        }

        // Generate order number if not provided
        if (!isset($attributes['order_number'])) {
            $attributes['order_number'] = static::generateOrderNumber($attributes['store_id']);
        }

        return static::query()->create($attributes);
    }

    protected static function generateOrderNumber($storeId)
    {
        return DB::transaction(function () use ($storeId) {
            $year = date('Y');
            $latestOrder = static::withoutGlobalScopes()
                ->where('store_id', $storeId)
                ->whereYear('created_at', $year)
                ->where('order_number', 'like', 'SO-' . $year . '-%')
                ->lockForUpdate()
                ->orderBy('order_number', 'desc')
                ->first();

            if ($latestOrder) {
                $lastNumber = intval(substr($latestOrder->order_number, -6));
                $newNumber = $lastNumber + 1;
            } else {
                $newNumber = 1;
            }

            return 'SO-' . $year . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        });
    }

  protected static function boot()
    {
        parent::boot();

        static::saved(function ($salesOrder) {
            $salesOrder->calculateTotals();
        });
    }
}
