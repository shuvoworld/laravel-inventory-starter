<?php

namespace App\Http\Controllers\Api;

use App\Modules\BlogCategory\Models\BlogCategory;
use Orion\Http\Controllers\Controller as OrionController;

class BlogCategoriesController extends OrionController
{
    protected $model = BlogCategory::class;

    protected bool $authorize = false;

    protected array $searchable = ['name'];
    protected array $filterable = ['id', 'name', 'created_at', 'updated_at'];
    protected array $sortable = ['id', 'name', 'created_at', 'updated_at'];
}
