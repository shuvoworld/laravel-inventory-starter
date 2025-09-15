<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()->with(['roles', 'permissions'])->orderBy('name')->get();
        $roles = Role::query()->with('permissions')->orderBy('name')->get();
        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.permissions.index', compact('users', 'roles', 'permissions'));
    }

    public function assignRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);
        $user->assignRole($data['role']);
        return back()->with('success', __('Role assigned.'));
    }

    public function revokeRole(Request $request, User $user, Role $role): RedirectResponse
    {
        $user->removeRole($role->name);
        return back()->with('success', __('Role revoked.'));
    }

    public function givePermission(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permission' => ['required', 'string', 'exists:permissions,name'],
        ]);
        $role->givePermissionTo($data['permission']);
        return back()->with('success', __('Permission granted to role.'));
    }

    public function revokePermission(Role $role, Permission $permission): RedirectResponse
    {
        $role->revokePermissionTo($permission->name);
        return back()->with('success', __('Permission revoked from role.'));
    }

    public function createRole(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);
        Role::create(['name' => $data['name'], 'guard_name' => config('auth.defaults.guard', 'web')]);
        return back()->with('success', __('Role created.'));
    }

    public function deleteRole(Role $role): RedirectResponse
    {
        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();
        });
        return back()->with('success', __('Role deleted.'));
    }

    public function createPermission(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);
        Permission::create(['name' => $data['name'], 'guard_name' => config('auth.defaults.guard', 'web')]);
        return back()->with('success', __('Permission created.'));
    }

    public function deletePermission(Permission $permission): RedirectResponse
    {
        DB::transaction(function () use ($permission) {
            $permission->roles()->detach();
            $permission->delete();
        });
        return back()->with('success', __('Permission deleted.'));
    }

    public function createModulePermissions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'module' => ['required', 'string', 'max:255'],
        ]);
        $name = strtolower(preg_replace('/[^a-z0-9_\-.]+/i', '-', $data['module']));
        $name = trim($name, '-_.');
        if ($name === '') {
            return back()->withErrors(['module' => __('Invalid module name')]);
        }

        $guard = config('auth.defaults.guard', 'web');
        $crud = ['view', 'create', 'edit', 'delete'];
        foreach ($crud as $action) {
            Permission::firstOrCreate(['name' => "$name.$action", 'guard_name' => $guard]);
        }

        return back()->with('success', __('Module CRUD permissions created for ":name"', ['name' => $name]));
    }
}
