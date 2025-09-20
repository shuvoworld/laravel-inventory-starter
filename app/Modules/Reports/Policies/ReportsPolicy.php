<?php

namespace App\Modules\Reports\Policies;

use App\Support\Modules\BaseModulePolicy;

class ReportsPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "reports.*" permissions
    protected string $module = 'reports';
}
