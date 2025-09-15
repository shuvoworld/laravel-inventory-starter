<?php

namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('users::index');
    }

    public function data(Request $request)
    {
        $query = User::query()->with('roles');

        return DataTables::eloquent($query)
            ->addColumn('roles', function (User $user) {
                return $user->roles->pluck('name')->join(', ');
            })
            ->addColumn('actions', function (User $user) {
                return view('users::partials.actions', compact('user'))->render();
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('users::create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        return redirect()->route('modules.users.index')->with('success', 'User created successfully');
    }

    public function show(int $id)
    {
        $user = User::with('roles')->findOrFail($id);
        return view('users::show', compact('user'));
    }

    public function edit(int $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::orderBy('name')->get();
        return view('users::edit', compact('user', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        if (array_key_exists('role', $validated)) {
            if (!empty($validated['role'])) {
                $user->syncRoles([$validated['role']]);
            } else {
                $user->syncRoles([]);
            }
        }

        return redirect()->route('modules.users.index')->with('success', 'User updated successfully');
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        // prevent self-delete for safety
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('modules.users.index')->with('success', 'User deleted successfully');
    }
}
