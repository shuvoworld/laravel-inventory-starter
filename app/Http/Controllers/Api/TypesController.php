<?php

namespace App\Http\Controllers\Api;

use App\Modules\Types\Models\Type;
use Orion\Http\Controllers\Controller as OrionController;

class TypesController extends OrionController
{
    protected $model = Type::class;

    protected bool $authorize = false;

    protected array $searchable = ['name'];
    protected array $filterable = ['id', 'name', 'created_at', 'updated_at'];
    protected array $sortable = ['id', 'name', 'created_at', 'updated_at'];
}
