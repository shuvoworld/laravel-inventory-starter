<?php

namespace App\Modules\Roles\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends Controller
{
    public function index()
    {
        return view('roles::index');
    }

    public function data(Request $request)
    {
        $query = Role::query()->with('permissions');

        return DataTables::eloquent($query)
            ->addColumn('permissions', function (Role $role) {
                return $role->permissions->pluck('name')->join(', ');
            })
            ->addColumn('actions', function (Role $role) {
                return view('roles::partials.actions', compact('role'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    protected function getGroupedPermissions()
    {
        $permissions = Permission::orderBy('name')->get();
        return $permissions->groupBy(function ($perm) {
            return Str::before($perm->name, '.');
        });
    }

    public function create()
    {
        $groupedPermissions = $this->getGroupedPermissions();
        return view('roles::create', compact('groupedPermissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('modules.roles.index')->with('status', 'Role created successfully');
    }

    public function show(int $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return view('roles::show', compact('role'));
    }

    public function edit(int $id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $groupedPermissions = $this->getGroupedPermissions();
        $selected = $role->permissions->pluck('name')->all();
        return view('roles::edit', compact('role', 'groupedPermissions', 'selected'));
    }

    public function update(Request $request, int $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,' . $role->id],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->name = $validated['name'];
        $role->save();

        // Sync permissions
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('modules.roles.index')->with('status', 'Role updated successfully');
    }

    public function destroy(int $id)
    {
        $role = Role::findOrFail($id);
        // Protect core admin role name if desired
        if ($role->name === 'admin') {
            return back()->with('status', 'Cannot delete core admin role.');
        }
        $role->delete();
        return redirect()->route('modules.roles.index')->with('status', 'Role deleted successfully');
    }
}
