<?php

namespace App\Modules\SalesOrder\Policies;

use App\Support\Modules\BaseModulePolicy;

class SalesOrderPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "sales-order.*" permissions
    protected string $module = 'sales-order';
}
