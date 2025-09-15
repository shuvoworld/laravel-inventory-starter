<?php

namespace App\Modules\BlogCategory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class BlogCategory extends Model implements AuditableContract
{
    use HasFactory, Auditable;

    protected $table = 'blog_categories';

    protected $fillable = [
        'name', // example field; adjust as needed
    ];

    protected $auditInclude = [
        'name',
    ];
}
