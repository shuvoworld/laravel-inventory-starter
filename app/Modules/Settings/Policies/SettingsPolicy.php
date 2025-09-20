<?php

namespace App\Modules\Settings\Policies;

use App\Support\Modules\BaseModulePolicy;

class SettingsPolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "settings.*" permissions
    protected string $module = 'settings';
}
