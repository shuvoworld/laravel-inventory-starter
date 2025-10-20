@php
    use App\Models\Module;use App\Modules\Settings\Models\Settings;
    $storeLogo = Settings::get('store_logo');
    $storeName = Settings::get('store_name', config('app.name'));
@endphp

<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
       class="relative z-20 bg-white border-end sidebar-transition overflow-hidden">
    <div class="h-100 d-flex flex-column">
        <!-- Store Logo -->
        <div class="p-3 border-bottom" x-show="sidebarOpen">
            <div class="text-center">
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

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-3">
            <ul class="nav nav-pills flex-column small px-2 gap-1">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('dashboard*') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="fas fa-house me-2"></i>
                        <span x-show="sidebarOpen">Dashboard</span>
                    </a>
                </li>

                <!-- Point of Sale -->
                @can('sales-order.create')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('pos.*') ? 'active' : '' }}"
                           href="{{ route('pos.index') }}" target="_blank">
                            <i class="fas fa-cash-register me-2"></i>
                            <span x-show="sidebarOpen">POS</span>
                            <span class="badge bg-success ms-auto" x-show="sidebarOpen">
                                <i class="fas fa-external-link-alt"></i>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('pos.pos2') ? 'active' : '' }}"
                           href="{{ route('pos.pos2') }}" target="_blank">
                            <i class="fas fa-cash-register me-2"></i>
                            <span x-show="sidebarOpen">POS-2</span>
                            <span class="badge bg-success ms-auto" x-show="sidebarOpen">
                                <i class="fas fa-external-link-alt"></i>
                            </span>
                        </a>
                    </li>
                @endcan

                <!-- Sales & Inventory -->
                <li class="nav-item mt-3 mb-2">
                    <span x-show="sidebarOpen" class="text-muted small fw-bold px-3">SALES & INVENTORY</span>
                </li>

                @can('customers.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.customers.*') ? 'active' : '' }}"
                           href="{{ route('modules.customers.index') }}">
                            <i class="fas fa-user-tie me-2"></i>
                            <span x-show="sidebarOpen">Customers</span>
                        </a>
                    </li>
                @endcan

                @can('products.view')
                    @php
                        $lowStockCount = \App\Modules\Products\Models\Product::lowStock()->count();
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.products.*') ? 'active' : '' }}"
                           href="{{ route('modules.products.index') }}">
                            <i class="fas fa-box me-2"></i>
                            <span x-show="sidebarOpen">Products</span>
                            @if($lowStockCount > 0)
                                <span class="badge bg-danger ms-auto" x-show="sidebarOpen">{{ $lowStockCount }}</span>
                            @endif
                        </a>
                    </li>
                @endcan

                @can('brand.view')
                    @if(Module::isActive('brand'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.brand.*') ? 'active' : '' }}"
                               href="{{ route('modules.brand.index') }}">
                                <i class="fas fa-copyright me-2"></i>
                                <span x-show="sidebarOpen">Brands</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('attribute-set.view')
                    @if(Module::isActive('attribute-set'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.attribute-set.*') ? 'active' : '' }}"
                               href="{{ route('modules.attribute-set.index') }}">
                                <i class="fas fa-layer-group me-2"></i>
                                <span x-show="sidebarOpen">Attribute Sets</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('product-attribute.view')
                    @if(Module::isActive('product-attribute'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.product-attribute.*') ? 'active' : '' }}"
                               href="{{ route('modules.product-attribute.index') }}">
                                <i class="fas fa-sliders-h me-2"></i>
                                <span x-show="sidebarOpen">Attributes</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('sales-order.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.sales-order.*') ? 'active' : '' }}"
                           href="{{ route('modules.sales-order.index') }}">
                            <i class="fas fa-shopping-cart me-2"></i>
                            <span x-show="sidebarOpen">Sales Orders</span>
                        </a>
                    </li>
                @endcan

                @can('sales-return.view')
                    @if(Module::isActive('sales-return'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.sales-return.*') ? 'active' : '' }}"
                               href="{{ route('modules.sales-return.index') }}">
                                <i class="fas fa-undo me-2"></i>
                                <span x-show="sidebarOpen">Sales Returns</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('purchase-order.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.purchase-order.*') ? 'active' : '' }}"
                           href="{{ route('modules.purchase-order.index') }}">
                            <i class="fas fa-truck me-2"></i>
                            <span x-show="sidebarOpen">Purchase Orders</span>
                        </a>
                    </li>
                @endcan

                @can('purchase-return.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.purchase-return.*') ? 'active' : '' }}"
                           href="{{ route('modules.purchase-return.index') }}">
                            <i class="fas fa-undo-alt me-2"></i>
                            <span x-show="sidebarOpen">Purchase Returns</span>
                        </a>
                    </li>
                @endcan

                @can('suppliers.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.suppliers.*') ? 'active' : '' }}"
                           href="{{ route('modules.suppliers.index') }}">
                            <i class="fas fa-handshake me-2"></i>
                            <span x-show="sidebarOpen">Suppliers</span>
                        </a>
                    </li>
                @endcan

                @can('stock-movement.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.*') ? 'active' : '' }}"
                           href="{{ route('modules.stock-movement.index') }}">
                            <i class="fas fa-exchange-alt me-2"></i>
                            <span x-show="sidebarOpen">Stock Movements</span>
                        </a>
                    </li>
                @endcan

                @can('operating-expenses.view')
                    @if(Module::isActive('operating-expenses'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.operating-expenses.*') ? 'active' : '' }}"
                               href="{{ route('modules.operating-expenses.index') }}">
                                <i class="fas fa-receipt me-2"></i>
                                <span x-show="sidebarOpen">Operating Expenses</span>
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Reports -->
                @can('reports.view')
                    @if(Module::isActive('reports'))
                        <li class="nav-item mt-3 mb-2">
                            <span x-show="sidebarOpen" class="text-muted small fw-bold px-3">REPORTS</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.*') && !request()->routeIs('modules.reports.stock*') ? 'active' : '' }}"
                               href="{{ route('modules.reports.index') }}">
                                <i class="fas fa-chart-line me-2"></i>
                                <span x-show="sidebarOpen">Reports</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.stock*') ? 'active' : '' }}"
                               href="{{ route('modules.reports.stock') }}">
                                <i class="fas fa-cubes me-2"></i>
                                <span x-show="sidebarOpen">Stock Report</span>
                            </a>
                        </li>
                    @endif
                @endcan

                <!-- Expenses -->
                @can('expense.view')
                    <li class="nav-item mt-3 mb-2">
                        <span x-show="sidebarOpen" class="text-muted small fw-bold px-3">EXPENSES</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.expenses.*') ? 'active' : '' }}"
                           href="{{ route('modules.expenses.index') }}">
                            <i class="fas fa-money-bill-wave me-2"></i>
                            <span x-show="sidebarOpen">Expenses</span>
                        </a>
                    </li>
                @endcan

                @can('expense-category.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.expense-category.*') ? 'active' : '' }}"
                           href="{{ route('modules.expense-category.index') }}">
                            <i class="fas fa-tags me-2"></i>
                            <span x-show="sidebarOpen">Expense Categories</span>
                        </a>
                    </li>
                @endcan

                <!-- Settings -->
                <li class="nav-item mt-3 mb-2">
                    <span x-show="sidebarOpen" class="text-muted small fw-bold px-3">SETTINGS</span>
                </li>

                @can('types.view')
                    @if(Module::isActive('types'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.types.*') ? 'active' : '' }}"
                               href="{{ route('modules.types.index') }}">
                                <i class="fas fa-tags me-2"></i>
                                <span x-show="sidebarOpen">Types</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('users.view')
                    @if(Module::isActive('users'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.users.*') ? 'active' : '' }}"
                               href="{{ route('modules.users.index') }}">
                                <i class="fas fa-users me-2"></i>
                                <span x-show="sidebarOpen">Users</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('roles.view')
                    @if(Module::isActive('roles'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.roles.*') ? 'active' : '' }}"
                               href="{{ route('modules.roles.index') }}">
                                <i class="fas fa-user-shield me-2"></i>
                                <span x-show="sidebarOpen">Roles</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('permissions.view')
                    @if(Module::isActive('permissions'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.permissions.*') ? 'active' : '' }}"
                               href="{{ route('modules.permissions.index') }}">
                                <i class="fas fa-key me-2"></i>
                                <span x-show="sidebarOpen">Permissions</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @can('settings.view')
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.settings.*') ? 'active' : '' }}"
                           href="{{ route('modules.settings.index') }}">
                            <i class="fas fa-cog me-2"></i>
                            <span x-show="sidebarOpen">Settings</span>
                        </a>
                    </li>
                @endcan

                @can('store-settings.view')
                    @if(Module::isActive('store-settings'))
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.store-settings.*') ? 'active' : '' }}"
                               href="{{ route('modules.store-settings.index') }}">
                                <i class="fas fa-store me-2"></i>
                                <span x-show="sidebarOpen">Store Settings</span>
                            </a>
                        </li>
                    @endif
                @endcan

                @role('admin')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}"
                       href="{{ route('admin.modules.index') }}">
                        <i class="fas fa-cubes me-2"></i>
                        <span x-show="sidebarOpen">Module Dictionary</span>
                    </a>
                </li>
                @endrole
            </ul>
        </nav>
    </div>
</aside>
