<?php

namespace App\Modules\Expense\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\BelongsToStore;

class Expense extends Model
{
    use HasFactory, BelongsToStore;

    protected $table = 'expenses';

    protected $fillable = [
        'store_id',
        'expense_category_id',
        'reference_number',
        'expense_date',
        'amount',
        'description',
        'payment_method',
        'receipt',
        'notes',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'store_id' => 'integer',
        'expense_category_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(\App\Modules\ExpenseCategory\Models\ExpenseCategory::class, 'expense_category_id');
    }

    public function store()
    {
        return $this->belongsTo(\App\Modules\Stores\Models\Store::class, 'store_id');
    }

    public function scopeForCurrentStore($query)
    {
        return $query->where('store_id', auth()->user()->currentStoreId());
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getFormattedExpenseDateAttribute()
    {
        return Carbon::parse($this->expense_date)->format('M d, Y');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}