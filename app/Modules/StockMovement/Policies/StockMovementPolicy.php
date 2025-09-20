<?php

namespace App\Modules\StockMovement\Policies;

use App\Support\Modules\BaseModulePolicy;

class StockMovementPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "stock-movement.*" permissions
    protected string $module = 'stock-movement';
}
