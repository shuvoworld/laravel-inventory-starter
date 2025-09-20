<?php

namespace App\Modules\SalesOrderItem\Policies;

use App\Support\Modules\BaseModulePolicy;

class SalesOrderItemPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "sales-order-item.*" permissions
    protected string $module = 'sales-order-item';
}
