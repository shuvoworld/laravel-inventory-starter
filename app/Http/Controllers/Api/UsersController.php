<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Orion\Http\Controllers\Controller as OrionController;
use Orion\Http\Requests\Request;

class UsersController extends OrionController
{
    /**
     * The model class that this controller handles.
     */
    protected $model = User::class;

    /**
     * Disable Orion's built-in authorization for now (guard via routes/middleware if needed).
     */
    protected bool $authorize = false;

    /**
     * Searchable columns for index endpoint.
     */
    protected array $searchable = ['name', 'email'];

    /**
     * Filterable columns for index endpoint.
     */
    protected array $filterable = ['id', 'name', 'email', 'created_at', 'updated_at'];

    /**
     * Sortable columns for index endpoint.
     */
    protected array $sortable = ['id', 'name', 'email', 'created_at', 'updated_at'];

    /**
     * Relations that can be included via include[] query parameter.
     */
    protected array $includes = ['roles'];

    /**
     * Optionally customize the base query.
     */
    protected function buildIndexFetchQuery(Request $request, array $requestedRelations)
    {
        return parent::buildIndexFetchQuery($request, $requestedRelations)->with('roles');
    }
}
