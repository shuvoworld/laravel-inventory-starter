<?php

namespace App\Modules\ProductAttribute\Policies;

use App\Support\Modules\BaseModulePolicy;

class ProductAttributePolicy extends BaseModulePolicy
{
    // Inherit CRUD checks from BaseModulePolicy mapping to "product-attribute.*" permissions
    protected string $module = 'product-attribute';
}
