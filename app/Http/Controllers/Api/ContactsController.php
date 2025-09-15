<?php

namespace App\Http\Controllers\Api;

use App\Modules\Contact\Models\Contact;
use Orion\Http\Controllers\Controller as OrionController;

class ContactsController extends OrionController
{
    protected $model = Contact::class;

    protected bool $authorize = false;

    protected array $searchable = ['name', 'email', 'phone'];
    protected array $filterable = ['id', 'name', 'email', 'phone', 'user_id', 'created_at', 'updated_at'];
    protected array $sortable = ['id', 'name', 'email', 'created_at', 'updated_at'];
}
