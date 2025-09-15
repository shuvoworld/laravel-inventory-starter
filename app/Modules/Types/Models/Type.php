<?php

namespace App\Modules\Types\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Type extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'types';

    protected $fillable = [
        'name',
    ];

    protected $auditInclude = [
        'name',
    ];
}
