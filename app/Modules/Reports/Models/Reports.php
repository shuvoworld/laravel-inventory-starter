<?php

namespace App\Modules\Reports\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Reports extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'reports';

    protected $fillable = [
        'name', // example field; adjust as needed
    ];

    protected $auditInclude = [
        'name',
    ];
}
