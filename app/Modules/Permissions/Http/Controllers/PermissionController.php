<?php

namespace App\Modules\Permissions\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends Controller
{
    public function index()
    {
        return view('permissions::index');
    }

    public function data(Request $request)
    {
        $query = Permission::query();

        $tz = optional(auth()->user())->timezone ?? config('app.timezone');

        return DataTables::eloquent($query)
            ->addColumn('actions', function (Permission $permission) {
                return view('permissions::partials.actions', compact('permission'))->render();
            })
            ->editColumn('created_at', function (Permission $permission) use ($tz) {
                return $permission->created_at?->timezone($tz)->format('Y-m-d H:i');
            })
            ->editColumn('updated_at', function (Permission $permission) use ($tz) {
                return $permission->updated_at?->timezone($tz)->format('Y-m-d H:i');
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create()
    {
        return view('permissions::create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
        ]);

        Permission::create(['name' => $validated['name']]);

        return redirect()->route('modules.permissions.index')->with('success', 'Permission created successfully');
    }

    public function show(int $id)
    {
        $permission = Permission::findOrFail($id);

        return view('permissions::show', compact('permission'));
    }

    public function edit(int $id)
    {
        $permission = Permission::findOrFail($id);

        return view('permissions::edit', compact('permission'));
    }

    public function update(Request $request, int $id)
    {
        $permission = Permission::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name,'.$permission->id],
        ]);

        $permission->name = $validated['name'];
        $permission->save();

        return redirect()->route('modules.permissions.index')->with('success', 'Permission updated successfully');
    }

    public function destroy(int $id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('modules.permissions.index')->with('success', 'Permission deleted successfully');
    }
}
