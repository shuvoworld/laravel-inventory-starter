<?php

namespace App\Modules\Customers\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
        'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country',
    ];
}