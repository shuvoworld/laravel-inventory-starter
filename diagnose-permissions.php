<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PERMISSION DIAGNOSTICS ===\n\n";

// Check total permissions
$totalPermissions = \Spatie\Permission\Models\Permission::count();
echo "Total Permissions in database: {$totalPermissions}\n\n";

// Check total roles
$totalRoles = \Spatie\Permission\Models\Role::count();
echo "Total Roles in database: {$totalRoles}\n\n";

// Check super-admin role
$superAdmin = \Spatie\Permission\Models\Role::where('name', 'super-admin')->first();
if ($superAdmin) {
    echo "Super-admin role FOUND\n";
    echo "Super-admin has {$superAdmin->permissions->count()} permissions\n\n";

    if ($superAdmin->permissions->count() > 0) {
        echo "Super-admin permissions:\n";
        foreach ($superAdmin->permissions as $permission) {
            echo "  - {$permission->name}\n";
        }
    } else {
        echo "WARNING: Super-admin has NO permissions!\n";
    }
} else {
    echo "ERROR: Super-admin role NOT FOUND!\n";
}

echo "\n";

// Check users with super-admin role
echo "Users with super-admin role:\n";
$superAdminUsers = \App\Models\User::role('super-admin')->get();
if ($superAdminUsers->count() > 0) {
    foreach ($superAdminUsers as $user) {
        echo "  - {$user->email} (ID: {$user->id})\n";
        echo "    Roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
        echo "    Direct permissions: " . $user->permissions->count() . "\n";
        echo "    All permissions (via roles): " . $user->getAllPermissions()->count() . "\n";
    }
} else {
    echo "  No users found with super-admin role!\n";
}

echo "\n=== END DIAGNOSTICS ===\n";