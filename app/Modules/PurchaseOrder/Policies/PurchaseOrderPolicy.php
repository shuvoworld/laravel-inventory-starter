<?php

namespace App\Modules\PurchaseOrder\Policies;

use App\Support\Modules\BaseModulePolicy;

class PurchaseOrderPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "purchase-order.*" permissions
    protected string $module = 'purchase-order';
}
