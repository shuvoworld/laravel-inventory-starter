            <aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
                class="relative z-20 bg-sidebar text-sidebar-foreground border-r border-gray-200 dark:border-gray-700 sidebar-transition overflow-hidden">
                <!-- Sidebar Content -->
                <div class="h-full flex flex-col">
                    <!-- Sidebar Menu -->
                    <nav class="flex-1 overflow-y-auto custom-scrollbar py-4">
                        <ul class="space-y-1 px-2">
                            <!-- Dashboard -->
                            <x-layouts.sidebar-link href="{{ route('dashboard') }}" icon='fas-house'
                                :active="request()->routeIs('dashboard*')">Dashboard</x-layouts.sidebar-link>

                            <!-- Modules -->
                            @can('types.view')
                                <x-layouts.sidebar-link href="{{ route('modules.types.index') }}" icon='fas-tags'
                                    :active="request()->routeIs('modules.types.*')">Types</x-layouts.sidebar-link>
                            @endcan

                            @can('blog-category.view')
                                <x-layouts.sidebar-link href="{{ route('modules.blog-category.index') }}" icon='fas-folder-tree'
                                    :active="request()->routeIs('modules.blog-category.*')">Blog Categories</x-layouts.sidebar-link>
                            @endcan

                            <!-- Access Control Group: Users, Roles, Permissions, Admin Roles & Permissions -->
                            @php($canUsers = auth()->user()?->can('users.view'))
                            @php($canRoles = auth()->user()?->can('roles.view'))
                            @php($canPerms = auth()->user()?->can('permissions.view'))
                            @php($isAdmin = auth()->user()?->hasRole('admin'))
                            @php($anyAccessItems = $canUsers || $canRoles || $canPerms || $isAdmin)
                            @if($anyAccessItems)
                                <li x-data="{ open: {{ request()->routeIs('modules.users.*') || request()->routeIs('modules.roles.*') || request()->routeIs('modules.permissions.*') || request()->routeIs('admin.permissions.*') ? 'true' : 'false' }} }">
                                    <button @click="open = !open" :class="{ 'justify-center': !sidebarOpen, 'justify-between': sidebarOpen }"
                                        class="w-full flex items-center text-sm rounded-md px-3 py-2 transition-colors duration-200 hover:bg-sidebar-accent hover:text-sidebar-accent-foreground">
                                        @svg('fas-user-shield', 'w-5 h-5 text-gray-500')
                                        <span x-show="sidebarOpen" class="ml-3 flex-1 text-left">Access Control</span>
                                        <svg x-show="sidebarOpen" :class="{ 'rotate-180': open }" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <ul x-show="open" x-transition class="mt-1 space-y-1 pl-8 pr-2">
                                        @if($canUsers)
                                            <x-layouts.sidebar-link href="{{ route('modules.users.index') }}" icon='fas-users'
                                                :active="request()->routeIs('modules.users.*')">Users</x-layouts.sidebar-link>
                                        @endif
                                        @if($canRoles)
                                            <x-layouts.sidebar-link href="{{ route('modules.roles.index') }}" icon='fas-user-shield'
                                                :active="request()->routeIs('modules.roles.*')">Roles</x-layouts.sidebar-link>
                                        @endif
                                        @if($canPerms)
                                            <x-layouts.sidebar-link href="{{ route('modules.permissions.index') }}" icon='fas-key'
                                                :active="request()->routeIs('modules.permissions.*')">Permissions</x-layouts.sidebar-link>
                                        @endif
                                        @if($isAdmin)
                                            <x-layouts.sidebar-link href="{{ route('admin.permissions.index') }}" icon='fas-shield-halved'
                                                :active="request()->routeIs('admin.permissions.*')">Roles & Permissions (Admin)</x-layouts.sidebar-link>
                                        @endif
                                    </ul>
                                </li>
                            @endif
                        </ul>
                    </nav>
                </div>
            </aside>
