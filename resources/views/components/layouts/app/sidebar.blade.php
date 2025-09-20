            <aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
                class="relative z-20 bg-white border-end sidebar-transition overflow-hidden">
                <div class="h-100 d-flex flex-column">
                    <!-- Store Logo -->
                    <div class="p-3 border-bottom" x-show="sidebarOpen">
                        <div class="text-center">
                            @php
                                $storeLogo = \App\Modules\Settings\Models\Settings::get('store_logo');
                                $storeName = \App\Modules\Settings\Models\Settings::get('store_name', config('app.name'));
                            @endphp
                            @if($storeLogo)
                                <img src="{{ asset('storage/' . $storeLogo) }}" alt="{{ $storeName }}" class="img-fluid mb-2" style="max-height: 60px;">
                            @else
                                <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center mb-2" style="height: 60px;">
                                    <i class="fas fa-store fa-2x"></i>
                                </div>
                            @endif
                            <h6 class="mb-0 text-truncate">{{ $storeName }}</h6>
                        </div>
                    </div>

                    <nav class="flex-1 overflow-y-auto py-3">
                        <ul class="nav nav-pills flex-column small px-2 gap-1">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                    <i class="fas fa-house me-2"></i>
                                    <span x-show="sidebarOpen">Dashboard</span>
                                </a>
                            </li>

                            @can('types.view')
                            @if(\App\Models\Module::isActive('types'))
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.types.*') ? 'active' : '' }}" href="{{ route('modules.types.index') }}">
                                    <i class="fas fa-tags me-2"></i>
                                    <span x-show="sidebarOpen">Types</span>
                                </a>
                            </li>
                            @endif
                            @endcan

                            @can('blog-category.view')
                            @if(\App\Models\Module::isActive('blog-category'))
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.blog-category.*') ? 'active' : '' }}" href="{{ route('modules.blog-category.index') }}">
                                    <i class="fas fa-folder-tree me-2"></i>
                                    <span x-show="sidebarOpen">Blog Categories</span>
                                </a>
                            </li>
                            @endif
                            @endcan


                            @can('users.view')
                            @if(\App\Models\Module::isActive('users'))
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.users.*') ? 'active' : '' }}" href="{{ route('modules.users.index') }}">
                                    <i class="fas fa-users me-2"></i>
                                    <span x-show="sidebarOpen">Users</span>
                                </a>
                            </li>
                            @endif
                            @endcan

                            @can('roles.view')
                            @if(\App\Models\Module::isActive('roles'))
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.roles.*') ? 'active' : '' }}" href="{{ route('modules.roles.index') }}">
                                    <i class="fas fa-user-shield me-2"></i>
                                    <span x-show="sidebarOpen">Roles</span>
                                </a>
                            </li>
                            @endif
                            @endcan

                            @can('permissions.view')
                            @if(\App\Models\Module::isActive('permissions'))
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.permissions.*') ? 'active' : '' }}" href="{{ route('modules.permissions.index') }}">
                                    <i class="fas fa-key me-2"></i>
                                    <span x-show="sidebarOpen">Permissions</span>
                                </a>
                            </li>
                            @endif
                            @endcan

                            <!-- Sales & Inventory Section -->
                            <li class="nav-item mt-3 mb-2">
                                <span x-show="sidebarOpen" class="text-muted small fw-bold px-3">SALES & INVENTORY</span>
                            </li>

                            @can('customers.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.customers.*') ? 'active' : '' }}" href="{{ route('modules.customers.index') }}">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <span x-show="sidebarOpen">Customers</span>
                                </a>
                            </li>
                            @endcan

                            @can('products.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.products.*') ? 'active' : '' }}" href="{{ route('modules.products.index') }}">
                                    <i class="fas fa-box me-2"></i>
                                    <span x-show="sidebarOpen">Products</span>
                                </a>
                            </li>
                            @endcan

                            @can('sales-order.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.sales-order.*') ? 'active' : '' }}" href="{{ route('modules.sales-order.index') }}">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    <span x-show="sidebarOpen">Sales Orders</span>
                                </a>
                            </li>
                            @endcan

                            @can('purchase-order.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.purchase-order.*') ? 'active' : '' }}" href="{{ route('modules.purchase-order.index') }}">
                                    <i class="fas fa-truck me-2"></i>
                                    <span x-show="sidebarOpen">Purchase Orders</span>
                                </a>
                            </li>
                            @endcan

                            @can('stock-movement.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.*') ? 'active' : '' }}" href="{{ route('modules.stock-movement.index') }}">
                                    <i class="fas fa-exchange-alt me-2"></i>
                                    <span x-show="sidebarOpen">Stock Movements</span>
                                </a>
                            </li>
                            @endcan

                            <!-- Settings Section -->
                            @can('settings.view')
                            <li class="nav-item mt-3 mb-2">
                                <span x-show="sidebarOpen" class="text-muted small fw-bold px-3">SETTINGS</span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.settings.*') ? 'active' : '' }}" href="{{ route('modules.settings.index') }}">
                                    <i class="fas fa-cog me-2"></i>
                                    <span x-show="sidebarOpen">Store Settings</span>
                                </a>
                            </li>
                            @endcan

                        @role('admin')
                            <li class="nav-item mt-2">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}" href="{{ route('admin.modules.index') }}">
                                    <i class="fas fa-cubes me-2"></i>
                                    <span x-show="sidebarOpen">Module Dictionary</span>
                                </a>
                            </li>
                        @endrole
                        </ul>
                    </nav>
                </div>
            </aside>
