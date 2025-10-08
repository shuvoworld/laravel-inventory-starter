<?php

namespace App\Modules\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToStore;

class Customer extends Model
{
    use BelongsToStore;

    protected $table = 'customers';

    protected $fillable = [
        'store_id', 'name', 'email', 'phone', 'address', 'city', 'state', 'postal_code', 'country',
    ];
}