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

        // Filter users to only show users from the same store (unless superadmin)
        if (!auth()->user()->isSuperAdmin()) {
            $storeId = auth()->user()->currentStoreId();
            $query->where('store_id', $storeId);
        }

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
        // Store-admins can only assign store-user role
        // Superadmins can assign any role
        if (auth()->user()->isSuperAdmin()) {
            $roles = Role::orderBy('name')->get();
        } else {
            $roles = Role::whereIn('name', ['store-user'])->get();
        }

        return view('users::create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        // Only allow superadmin field if current user is superadmin
        if (auth()->user()->isSuperAdmin()) {
            $validationRules['is_superadmin'] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($validationRules);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        // Only set is_superadmin if current user is superadmin
        if (auth()->user()->isSuperAdmin() && isset($validated['is_superadmin'])) {
            $userData['is_superadmin'] = (bool) $validated['is_superadmin'];
        }

        // Set store_id for non-superadmin creators
        if (!auth()->user()->isSuperAdmin()) {
            $userData['store_id'] = auth()->user()->currentStoreId();
        }

        $user = User::create($userData);

        // Handle optional profile picture upload
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->profile_photo_path = $path;
            $user->save();
        }

        // Assign role (default to store-user if created by store-admin)
        if (!empty($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        } elseif (!auth()->user()->isSuperAdmin()) {
            // Default to store-user for non-superadmin creators
            $user->syncRoles(['store-user']);
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

        // Ensure user belongs to same store (unless superadmin)
        if (!auth()->user()->isSuperAdmin()) {
            $storeId = auth()->user()->currentStoreId();
            if ($user->store_id !== $storeId) {
                abort(403, 'Unauthorized action.');
            }
        }

        // Store-admins can only assign store-user role
        if (auth()->user()->isSuperAdmin()) {
            $roles = Role::orderBy('name')->get();
        } else {
            $roles = Role::whereIn('name', ['store-user'])->get();
        }

        return view('users::edit', compact('user', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', 'min:6'],
            'role' => ['nullable', 'string', 'exists:roles,name'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        // Only allow superadmin field if current user is superadmin
        if (auth()->user()->isSuperAdmin()) {
            $validationRules['is_superadmin'] = ['nullable', 'boolean'];
        }

        $validated = $request->validate($validationRules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Only update is_superadmin if current user is superadmin
        if (auth()->user()->isSuperAdmin() && array_key_exists('is_superadmin', $validated)) {
            $user->is_superadmin = (bool) ($validated['is_superadmin'] ?? false);
        }
        // Handle optional profile picture upload (replace existing)
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->profile_photo_path = $path;
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

        // Ensure user belongs to same store (unless superadmin)
        if (!auth()->user()->isSuperAdmin()) {
            $storeId = auth()->user()->currentStoreId();
            if ($user->store_id !== $storeId) {
                abort(403, 'Unauthorized action.');
            }
        }

        $user->delete();
        return redirect()->route('modules.users.index')->with('success', 'User deleted successfully');
    }
}
