<?php

namespace App\Modules\Contact\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Contact extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'contacts';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'user_id',
    ];

    protected $auditInclude = [
        'name', 'email', 'phone', 'user_id',
    ];
}
