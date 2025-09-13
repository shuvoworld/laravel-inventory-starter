<x-layouts.app>
    <div class="space-y-8">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Roles & Permissions</h1>
            <div class="text-sm text-gray-500">Spatie Laravel Permission</div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Users & Roles -->
            <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-medium mb-4">Users</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Name</th>
                                <th class="py-2 pr-4">Email</th>
                                <th class="py-2 pr-4">Roles</th>
                                <th class="py-2 pr-4">Assign Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="py-2 pr-4">{{ $user->name }}</td>
                                    <td class="py-2 pr-4">{{ $user->email }}</td>
                                    <td class="py-2 pr-4">
                                        <div class="flex flex-wrap gap-2">
                                            @forelse($user->roles as $role)
                                                <form method="POST" action="{{ route('admin.permissions.users.revoke-role', [$user, $role]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="px-2 py-1 rounded bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-200 hover:bg-blue-200" title="Revoke role">
                                                        {{ $role->name }} ✕
                                                    </button>
                                                </form>
                                            @empty
                                                <span class="text-gray-400">—</span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="py-2 pr-4">
                                        <form method="POST" action="{{ route('admin.permissions.users.assign-role', $user) }}" class="flex gap-2">
                                            @csrf
                                            <select name="role" class="border rounded px-2 py-1 bg-white dark:bg-gray-900">
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="px-3 py-1 rounded bg-green-600 text-white hover:bg-green-700">Assign</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Create Roles/Permissions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-6">
                <div>
                    <h2 class="text-lg font-medium mb-3">Create Role</h2>
                    <form method="POST" action="{{ route('admin.permissions.roles.create') }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="name" placeholder="role name" class="border rounded px-2 py-1 flex-1 bg-white dark:bg-gray-900" required>
                        <button class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Create</button>
                    </form>
                    @error('name')
                        <div class="text-red-500 text-xs mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <h2 class="text-lg font-medium mb-3">Create Permission</h2>
                    <form method="POST" action="{{ route('admin.permissions.permissions.create') }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="name" placeholder="permission name (e.g. types.publish)" class="border rounded px-2 py-1 flex-1 bg-white dark:bg-gray-900" required>
                        <button class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Create</button>
                    </form>
                </div>
                <div>
                    <h2 class="text-lg font-medium mb-3">Create Module CRUD Permissions</h2>
                    <form method="POST" action="{{ route('admin.permissions.modules.create') }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="module" placeholder="module name (e.g. types, categories)" class="border rounded px-2 py-1 flex-1 bg-white dark:bg-gray-900" required>
                        <button class="px-3 py-1 rounded bg-indigo-600 text-white hover:bg-indigo-700">Generate</button>
                    </form>
                    <p class="text-xs text-gray-500 mt-1">Generates: <code>module.view, module.create, module.edit, module.delete</code></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Roles & Permissions mapping -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-medium mb-4">Roles</h2>
                <div class="space-y-4">
                    @foreach($roles as $role)
                        <div class="border border-gray-200 dark:border-gray-700 rounded p-3">
                            <div class="flex items-center justify-between">
                                <div class="font-medium">{{ $role->name }}</div>
                                <form method="POST" action="{{ route('admin.permissions.roles.delete', $role) }}" onsubmit="return confirm('Delete role {{ $role->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Delete</button>
                                </form>
                            </div>
                            <div class="mt-2 text-sm">
                                <div class="mb-2 font-semibold text-gray-600">Permissions</div>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($role->permissions as $perm)
                                        <form method="POST" action="{{ route('admin.permissions.roles.revoke-permission', [$role, $perm]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-2 py-1 rounded bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-200 hover:bg-purple-200" title="Revoke permission">
                                                {{ $perm->name }} ✕
                                            </button>
                                        </form>
                                    @empty
                                        <span class="text-gray-400">No permissions</span>
                                    @endforelse
                                </div>
                                <form method="POST" action="{{ route('admin.permissions.roles.give-permission', $role) }}" class="mt-3 flex gap-2">
                                    @csrf
                                    <select name="permission" class="border rounded px-2 py-1 bg-white dark:bg-gray-900">
                                        @foreach($permissions as $permission)
                                            <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                        @endforeach
                                    </select>
                                    <button class="px-3 py-1 rounded bg-green-600 text-white hover:bg-green-700">Grant</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- All Permissions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <h2 class="text-lg font-medium mb-4">All Permissions</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-500">
                            <tr>
                                <th class="py-2 pr-4">Permission</th>
                                <th class="py-2 pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissions as $permission)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="py-2 pr-4">{{ $permission->name }}</td>
                                    <td class="py-2 pr-4">
                                        <form method="POST" action="{{ route('admin.permissions.permissions.delete', $permission) }}" onsubmit="return confirm('Delete permission {{ $permission->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
