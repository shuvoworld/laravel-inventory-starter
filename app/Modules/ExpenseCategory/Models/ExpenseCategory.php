<?php

namespace App\Modules\ExpenseCategory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $table = 'expense_categories';

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function expenses()
    {
        return $this->hasMany(\App\Modules\Expense\Models\Expense::class);
    }
}