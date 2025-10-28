<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class VerifySuperAdminPermissions extends Command
{
    protected $signature = 'permissions:verify-superadmin';
    protected $description = 'Verify and fix super-admin permissions';

    public function handle(): int
    {
        $guard = config('auth.defaults.guard', 'web');

        // Get super-admin role
        $superAdmin = Role::where('name', 'super-admin')->where('guard_name', $guard)->first();

        if (!$superAdmin) {
            $this->error('Super-admin role not found!');
            return 1;
        }

        // Get all permissions
        $allPermissions = Permission::where('guard_name', $guard)->get();

        $this->info("Total permissions in database: " . $allPermissions->count());
        $this->info("Super-admin current permissions: " . $superAdmin->permissions->count());

        // Assign all permissions to super-admin
        $superAdmin->syncPermissions($allPermissions);

        $this->info("Super-admin now has: " . $superAdmin->permissions->count() . " permissions");

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->info('✓ Permission cache cleared!');

        $this->newLine();
        $this->warn('IMPORTANT: Users must LOG OUT and LOG BACK IN to see the changes!');
        $this->newLine();

        return 0;
    }
}