<?php

namespace App\Modules\Contact\Policies;

use App\Support\Modules\BaseModulePolicy;

class ContactPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "contact.*" permissions
    protected string $module = 'contact';
}
