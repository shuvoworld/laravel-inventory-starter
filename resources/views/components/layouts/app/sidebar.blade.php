            <aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
                class="relative z-20 bg-white border-end sidebar-transition overflow-hidden">
                <div class="h-100 d-flex flex-column">
                    <nav class="flex-1 overflow-y-auto py-3">
                        <ul class="nav nav-pills flex-column small px-2 gap-1">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="fas fa-house me-2"></i>
                                    <span x-show="sidebarOpen">Dashboard</span>
                                </a>
                            </li>

                            @can('types.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.types.*') ? 'active' : '' }}" href="{{ route('modules.types.index') }}">
                                    <i class="fas fa-tags me-2"></i>
                                    <span x-show="sidebarOpen">Types</span>
                                </a>
                            </li>
                            @endcan

                            @can('blog-category.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.blog-category.*') ? 'active' : '' }}" href="{{ route('modules.blog-category.index') }}">
                                    <i class="fas fa-folder-tree me-2"></i>
                                    <span x-show="sidebarOpen">Blog Categories</span>
                                </a>
                            </li>
                            @endcan

                            @can('contact.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.contact.*') ? 'active' : '' }}" href="{{ route('modules.contact.index') }}">
                                    <i class="fas fa-address-book me-2"></i>
                                    <span x-show="sidebarOpen">Contacts</span>
                                </a>
                            </li>
                            @endcan

                            @can('users.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.users.*') ? 'active' : '' }}" href="{{ route('modules.users.index') }}">
                                    <i class="fas fa-users me-2"></i>
                                    <span x-show="sidebarOpen">Users</span>
                                </a>
                            </li>
                            @endcan

                            @can('roles.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.roles.*') ? 'active' : '' }}" href="{{ route('modules.roles.index') }}">
                                    <i class="fas fa-user-shield me-2"></i>
                                    <span x-show="sidebarOpen">Roles</span>
                                </a>
                            </li>
                            @endcan

                            @can('permissions.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.permissions.*') ? 'active' : '' }}" href="{{ route('modules.permissions.index') }}">
                                    <i class="fas fa-key me-2"></i>
                                    <span x-show="sidebarOpen">Permissions</span>
                                </a>
                            </li>
                            @endcan
                        </ul>
                    </nav>
                </div>
            </aside>
