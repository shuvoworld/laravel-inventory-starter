<?php

namespace App\Modules\PurchaseOrderItem\Policies;

use App\Support\Modules\BaseModulePolicy;

class PurchaseOrderItemPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "purchase-order-item.*" permissions
    protected string $module = 'purchase-order-item';
}
