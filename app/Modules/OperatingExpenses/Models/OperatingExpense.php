<?php

namespace App\Modules\OperatingExpenses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OperatingExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_number',
        'category',
        'description',
        'amount',
        'expense_date',
        'payment_status',
        'frequency',
        'vendor',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (!$expense->expense_number) {
                $expense->expense_number = static::generateExpenseNumber();
            }
        });
    }

    /**
     * Generate a unique expense number.
     */
    public static function generateExpenseNumber(): string
    {
        $prefix = 'EXP';
        $date = Carbon::now()->format('Ymd');
        $count = static::whereDate('created_at', Carbon::today())->count() + 1;

        return $prefix . $date . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Get expense categories.
     */
    public static function getCategories(): array
    {
        return [
            'rent' => 'Rent & Lease',
            'utilities' => 'Utilities',
            'salaries' => 'Salaries & Wages',
            'marketing' => 'Marketing & Advertising',
            'maintenance' => 'Maintenance & Repairs',
            'insurance' => 'Insurance',
            'office_supplies' => 'Office Supplies',
            'professional_services' => 'Professional Services',
            'travel' => 'Travel & Transportation',
            'telecommunications' => 'Telecommunications',
            'software' => 'Software & Subscriptions',
            'equipment' => 'Equipment',
            'training' => 'Training & Development',
            'legal' => 'Legal Fees',
            'accounting' => 'Accounting Fees',
            'other' => 'Other',
        ];
    }

    /**
     * Get payment statuses.
     */
    public static function getPaymentStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
        ];
    }

    /**
     * Get frequencies.
     */
    public static function getFrequencies(): array
    {
        return [
            'one_time' => 'One Time',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'yearly' => 'Yearly',
        ];
    }

    /**
     * Get the category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        $categories = static::getCategories();
        return $categories[$this->category] ?? ucfirst($this->category);
    }

    /**
     * Get the payment status label.
     */
    public function getPaymentStatusLabelAttribute(): string
    {
        $statuses = static::getPaymentStatuses();
        return $statuses[$this->payment_status] ?? ucfirst($this->payment_status);
    }

    /**
     * Get the frequency label.
     */
    public function getFrequencyLabelAttribute(): string
    {
        $frequencies = static::getFrequencies();
        return $frequencies[$this->frequency] ?? ucfirst($this->frequency);
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    /**
     * Scope for filtering by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for filtering by payment status.
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Get expenses for profit/loss calculation.
     */
    public static function getExpensesForPeriod($startDate, $endDate): float
    {
        return static::inDateRange($startDate, $endDate)
            ->where('payment_status', 'paid')
            ->sum('amount');
    }

    /**
     * Get expenses grouped by category for period.
     */
    public static function getExpensesByCategoryForPeriod($startDate, $endDate): array
    {
        $expenses = static::inDateRange($startDate, $endDate)
            ->where('payment_status', 'paid')
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->get();

        $categories = static::getCategories();
        $result = [];

        foreach ($expenses as $expense) {
            $result[] = [
                'category' => $expense->category,
                'category_label' => $categories[$expense->category] ?? ucfirst($expense->category),
                'total' => $expense->total,
            ];
        }

        return $result;
    }
}