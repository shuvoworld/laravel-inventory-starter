@php
    use App\Models\Module;use App\Modules\Settings\Models\Settings;
@endphp

<aside :class="{ 'w-full md:w-64': sidebarOpen, 'w-0 md:w-16 hidden md:block': !sidebarOpen }"
       class="relative z-20 bg-white border-end sidebar-transition overflow-hidden">
    <div class="h-100 d-flex flex-column">
        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto py-3" x-data="{
            openMenus: {
                pos: {{ request()->routeIs('pos.*') ? 'true' : 'false' }},
                products: {{ request()->routeIs('modules.products.*') || request()->routeIs('modules.brand.*') || request()->routeIs('modules.attribute-set.*') || request()->routeIs('modules.product-attribute.*') || request()->routeIs('modules.product-variant.*') || request()->routeIs('modules.variant-options.*') ? 'true' : 'false' }},
                sales: {{ request()->routeIs('modules.sales-order.*') || request()->routeIs('modules.sales-return.*') || request()->routeIs('modules.customers.*') ? 'true' : 'false' }},
                purchases: {{ request()->routeIs('modules.purchase-order.*') || request()->routeIs('modules.purchase-return.*') || request()->routeIs('modules.suppliers.*') ? 'true' : 'false' }},
                inventory: {{ request()->routeIs('modules.stock-movement.*') ? 'true' : 'false' }},
                expenses: {{ request()->routeIs('modules.operating-expenses.*') || request()->routeIs('modules.expenses.*') || request()->routeIs('modules.expense-category.*') ? 'true' : 'false' }},
                reports: {{ request()->routeIs('modules.reports.*') || request()->routeIs('reports.*') ? 'true' : 'false' }},
                settings: {{ request()->routeIs('modules.types.*') || request()->routeIs('modules.users.*') || request()->routeIs('modules.roles.*') || request()->routeIs('modules.permissions.*') || request()->routeIs('modules.settings.*') || request()->routeIs('modules.store-settings.*') || request()->routeIs('admin.modules.*') ? 'true' : 'false' }}
            }
        }">
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
                @if(auth()->user()->can('sales-order.create'))
                    <li class="nav-item" x-show="sidebarOpen">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('pos.*') ? 'active' : '' }}"
                           href="#"
                           @click.prevent="openMenus.pos = !openMenus.pos"
                           :class="{ 'active': openMenus.pos }">
                            <span>
                                <i class="fas fa-cash-register me-2"></i>
                                Point of Sale
                            </span>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.pos }"></i>
                        </a>
                    </li>
                    <ul x-show="sidebarOpen && openMenus.pos"
                        x-collapse
                        class="nav nav-pills flex-column small ps-4 gap-1">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('pos.index') ? 'active' : '' }}"
                               href="{{ route('pos.index') }}" target="_blank">
                                <i class="fas fa-external-link-alt me-2 text-success"></i>
                                <span>POS</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('pos.pos2') ? 'active' : '' }}"
                               href="{{ route('pos.pos2') }}" target="_blank">
                                <i class="fas fa-external-link-alt me-2 text-success"></i>
                                <span>POS-2</span>
                            </a>
                        </li>
                    </ul>
                @endif

                <!-- Products -->
                @if(auth()->user()->can('products.view') || auth()->user()->can('brand.view') || auth()->user()->can('attribute-set.view') || auth()->user()->can('product-attribute.view') || auth()->user()->can('variant-options.view') || auth()->user()->can('product-variant.view'))
                    <li class="nav-item" x-show="sidebarOpen">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('modules.products.*') || request()->routeIs('modules.brand.*') || request()->routeIs('modules.attribute-set.*') || request()->routeIs('modules.product-attribute.*') || request()->routeIs('modules.product-variant.*') || request()->routeIs('modules.variant-options.*') ? 'active' : '' }}"
                           href="#"
                           @click.prevent="openMenus.products = !openMenus.products"
                           :class="{ 'active': openMenus.products }">
                            <span>
                                <i class="fas fa-box me-2"></i>
                                Products
                            </span>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.products }"></i>
                        </a>
                    </li>
                    <ul x-show="sidebarOpen && openMenus.products"
                        x-collapse
                        class="nav nav-pills flex-column small ps-4 gap-1">
                        @can('products.view')
                            @php
                                $lowStockCount = \App\Modules\Products\Models\Product::lowStock()->count();
                            @endphp
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.products.*') ? 'active' : '' }}"
                                   href="{{ route('modules.products.index') }}">
                                    <i class="fas fa-box me-2"></i>
                                    <span>All Products</span>
                                    @if($lowStockCount > 0)
                                        <span class="badge bg-danger ms-auto">{{ $lowStockCount }}</span>
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
                                        <span>Brands</span>
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
                                        <span>Attribute Sets</span>
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
                                        <span>Attributes</span>
                                    </a>
                                </li>
                            @endif
                        @endcan

                        @can('variant-options.view')
                            @if(Module::isActive('products'))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.variant-options.*') ? 'active' : '' }}"
                                       href="{{ route('modules.variant-options.index') }}">
                                        <i class="fas fa-tags me-2"></i>
                                        <span>Variant Options</span>
                                    </a>
                                </li>
                            @endif
                        @endcan

                        @can('product-variant.view')
                            @if(Module::isActive('products'))
                                @php
                                    $lowStockVariantsCount = \App\Modules\Products\Models\ProductVariant::where('quantity_on_hand', '<=', \App\Modules\Products\Models\ProductVariant::whereNotNull('reorder_level')->first()?->reorder_level ?? 5)
                                        ->where('quantity_on_hand', '>', 0)
                                        ->where('is_active', true)
                                        ->count();
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.product-variant.*') ? 'active' : '' }}"
                                       href="{{ route('modules.product-variant.index') }}">
                                        <i class="fas fa-layer-group me-2"></i>
                                        <span>Product Variants</span>
                                        @if($lowStockVariantsCount > 0)
                                            <span class="badge bg-warning ms-auto">{{ $lowStockVariantsCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        @endcan
                    </ul>
                @endif

                <!-- Sales -->
                @if(auth()->user()->can('sales-order.view') || auth()->user()->can('sales-return.view') || auth()->user()->can('customers.view'))
                    <li class="nav-item" x-show="sidebarOpen">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('modules.sales-order.*') || request()->routeIs('modules.sales-return.*') || request()->routeIs('modules.customers.*') ? 'active' : '' }}"
                           href="#"
                           @click.prevent="openMenus.sales = !openMenus.sales"
                           :class="{ 'active': openMenus.sales }">
                            <span>
                                <i class="fas fa-shopping-cart me-2"></i>
                                Sales
                            </span>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.sales }"></i>
                        </a>
                    </li>
                    <ul x-show="sidebarOpen && openMenus.sales"
                        x-collapse
                        class="nav nav-pills flex-column small ps-4 gap-1">
                        @can('sales-order.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.sales-order.*') ? 'active' : '' }}"
                                   href="{{ route('modules.sales-order.index') }}">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    <span>Sales Orders</span>
                                </a>
                            </li>
                        @endcan

                        @can('sales-return.view')
                            @if(Module::isActive('sales-return'))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.sales-return.*') ? 'active' : '' }}"
                                       href="{{ route('modules.sales-return.index') }}">
                                        <i class="fas fa-undo me-2"></i>
                                        <span>Sales Returns</span>
                                    </a>
                                </li>
                            @endif
                        @endcan

                        @can('customers.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.customers.*') ? 'active' : '' }}"
                                   href="{{ route('modules.customers.index') }}">
                                    <i class="fas fa-user-tie me-2"></i>
                                    <span>Customers</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endif

                <!-- Purchases -->
                @if(auth()->user()->can('purchase-order.view') || auth()->user()->can('purchase-return.view') || auth()->user()->can('suppliers.view'))
                    <li class="nav-item" x-show="sidebarOpen">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('modules.purchase-order.*') || request()->routeIs('modules.purchase-return.*') || request()->routeIs('modules.suppliers.*') ? 'active' : '' }}"
                           href="#"
                           @click.prevent="openMenus.purchases = !openMenus.purchases"
                           :class="{ 'active': openMenus.purchases }">
                            <span>
                                <i class="fas fa-truck me-2"></i>
                                Purchases
                            </span>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.purchases }"></i>
                        </a>
                    </li>
                    <ul x-show="sidebarOpen && openMenus.purchases"
                        x-collapse
                        class="nav nav-pills flex-column small ps-4 gap-1">
                        @can('purchase-order.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.purchase-order.*') ? 'active' : '' }}"
                                   href="{{ route('modules.purchase-order.index') }}">
                                    <i class="fas fa-truck me-2"></i>
                                    <span>Purchase Orders</span>
                                </a>
                            </li>
                        @endcan

                        @can('purchase-return.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.purchase-return.*') ? 'active' : '' }}"
                                   href="{{ route('modules.purchase-return.index') }}">
                                    <i class="fas fa-undo-alt me-2"></i>
                                    <span>Purchase Returns</span>
                                </a>
                            </li>
                        @endcan

                        @can('suppliers.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.suppliers.*') ? 'active' : '' }}"
                                   href="{{ route('modules.suppliers.index') }}">
                                    <i class="fas fa-handshake me-2"></i>
                                    <span>Suppliers</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endif

                <!-- Inventory / Stock Movement -->
                @can('stock-movement.view')
                    <li class="nav-item" x-show="sidebarOpen">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('modules.stock-movement.*') ? 'active' : '' }}"
                           href="#"
                           @click.prevent="openMenus.inventory = !openMenus.inventory"
                           :class="{ 'active': openMenus.inventory }">
                            <span>
                                <i class="fas fa-exchange-alt me-2"></i>
                                Inventory
                            </span>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.inventory }"></i>
                        </a>
                    </li>
                    <ul x-show="sidebarOpen && openMenus.inventory"
                        x-collapse
                        class="nav nav-pills flex-column small ps-4 gap-1">
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.index') && !request()->routeIs('modules.stock-movement.create') && !request()->routeIs('modules.stock-movement.opening-balance') && !request()->routeIs('modules.stock-movement.correction*') && !request()->routeIs('modules.stock-movement.bulk-correction') && !request()->routeIs('modules.stock-movement.simple-report') && !request()->routeIs('modules.stock-movement.reconcile*') ? 'active' : '' }}"
                               href="{{ route('modules.stock-movement.index') }}">
                                <i class="fas fa-list me-2"></i>
                                <span>All Movements</span>
                            </a>
                        </li>

                        @can('stock-movement.create')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.create') ? 'active' : '' }}"
                                   href="{{ route('modules.stock-movement.create') }}">
                                    <i class="fas fa-plus me-2"></i>
                                    <span>Stock Adjustment</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.opening-balance') ? 'active' : '' }}"
                                   href="{{ route('modules.stock-movement.opening-balance') }}">
                                    <i class="fas fa-balance-scale me-2"></i>
                                    <span>Opening Balance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.correction.create') ? 'active' : '' }}"
                                   href="{{ route('modules.stock-movement.correction.create') }}">
                                    <i class="fas fa-wrench me-2"></i>
                                    <span>Stock Correction</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.bulk-correction') ? 'active' : '' }}"
                                   href="{{ route('modules.stock-movement.bulk-correction') }}">
                                    <i class="fas fa-list-alt me-2"></i>
                                    <span>Bulk Correction</span>
                                </a>
                            </li>
                        @endcan

                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.simple-report') ? 'active' : '' }}"
                               href="{{ route('modules.stock-movement.simple-report') }}">
                                <i class="fas fa-table me-2"></i>
                                <span>Transaction Report</span>
                            </a>
                        </li>

                        @can('stock-movement.reconcile')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.stock-movement.reconcile') && !request()->routeIs('modules.stock-movement.reconcile.test') ? 'active' : '' }}"
                                   href="{{ route('modules.stock-movement.reconcile') }}">
                                    <i class="fas fa-sync me-2"></i>
                                    <span>Stock Reconciliation</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endcan

                <!-- Expenses -->
                @if(auth()->user()->can('operating-expenses.view') || auth()->user()->can('expense.view') || auth()->user()->can('expense-category.view'))
                    <li class="nav-item" x-show="sidebarOpen">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('modules.operating-expenses.*') || request()->routeIs('modules.expenses.*') || request()->routeIs('modules.expense-category.*') ? 'active' : '' }}"
                           href="#"
                           @click.prevent="openMenus.expenses = !openMenus.expenses"
                           :class="{ 'active': openMenus.expenses }">
                            <span>
                                <i class="fas fa-receipt me-2"></i>
                                Expenses
                            </span>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.expenses }"></i>
                        </a>
                    </li>
                    <ul x-show="sidebarOpen && openMenus.expenses"
                        x-collapse
                        class="nav nav-pills flex-column small ps-4 gap-1">
                        @can('operating-expenses.view')
                            @if(Module::isActive('operating-expenses'))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.operating-expenses.*') ? 'active' : '' }}"
                                       href="{{ route('modules.operating-expenses.index') }}">
                                        <i class="fas fa-receipt me-2"></i>
                                        <span>Operating Expenses</span>
                                    </a>
                                </li>
                            @endif
                        @endcan

                        @can('expense.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.expenses.*') ? 'active' : '' }}"
                                   href="{{ route('modules.expenses.index') }}">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <span>Expenses</span>
                                </a>
                            </li>
                        @endcan

                        @can('expense-category.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.expense-category.*') ? 'active' : '' }}"
                                   href="{{ route('modules.expense-category.index') }}">
                                    <i class="fas fa-tags me-2"></i>
                                    <span>Expense Categories</span>
                                </a>
                            </li>
                        @endcan
                    </ul>
                @endif

                <!-- Reports -->
                @can('reports.view')
                    @if(Module::isActive('reports'))
                        <li class="nav-item" x-show="sidebarOpen">
                            <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('modules.reports.*') ? 'active' : '' }}"
                               href="#"
                               @click.prevent="openMenus.reports = !openMenus.reports"
                               :class="{ 'active': openMenus.reports }">
                                <span>
                                    <i class="fas fa-chart-line me-2"></i>
                                    Reports
                                </span>
                                <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.reports }"></i>
                            </a>
                        </li>
                        <ul x-show="sidebarOpen && openMenus.reports"
                            x-collapse
                            class="nav nav-pills flex-column small ps-4 gap-1">
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.*') && !request()->routeIs('modules.reports.daily-sales*') && !request()->routeIs('modules.reports.daily-purchase*') && !request()->routeIs('modules.reports.weekly-performance*') && !request()->routeIs('modules.reports.low-stock-alert*') && !request()->routeIs('modules.reports.stock*') && !request()->routeIs('modules.reports.supplier-due*') && !request()->routeIs('modules.reports.customer-due*') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.index') }}">
                                    <i class="fas fa-chart-line me-2"></i>
                                    <span>Financial Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.daily-sales*') || request()->routeIs('reports.daily-sales') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.daily-sales') }}">
                                    <i class="fas fa-calendar-day me-2"></i>
                                    <span>Daily Sales Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.daily-purchase*') || request()->routeIs('reports.daily-purchase') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.daily-purchase') }}">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    <span>Daily Purchase Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.weekly-performance*') || request()->routeIs('reports.weekly-performance') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.weekly-performance') }}">
                                    <i class="fas fa-calendar-week me-2"></i>
                                    <span>Weekly Performance</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.low-stock-alert*') || request()->routeIs('reports.low-stock-alert') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.low-stock-alert') }}">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span>Low Stock Alert</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.supplier-due*') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.supplier-due') }}">
                                    <i class="fas fa-user-friends me-2"></i>
                                    <span>Supplier Due Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.customer-due*') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.customer-due') }}">
                                    <i class="fas fa-users me-2"></i>
                                    <span>Customer Due Report</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.reports.stock*') ? 'active' : '' }}"
                                   href="{{ route('modules.reports.stock') }}">
                                    <i class="fas fa-cubes me-2"></i>
                                    <span>Stock Report</span>
                                </a>
                            </li>
                        </ul>
                    @endif
                @endcan

                <!-- Settings -->
                @if(auth()->user()->can('types.view') || auth()->user()->can('users.view') || auth()->user()->can('roles.view') || auth()->user()->can('permissions.view') || auth()->user()->can('settings.view') || auth()->user()->can('store-settings.view') || auth()->user()->hasRole('admin'))
                    <li class="nav-item mt-3" x-show="sidebarOpen">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('modules.types.*') || request()->routeIs('modules.users.*') || request()->routeIs('modules.roles.*') || request()->routeIs('modules.permissions.*') || request()->routeIs('modules.settings.*') || request()->routeIs('modules.store-settings.*') || request()->routeIs('admin.modules.*') ? 'active' : '' }}"
                           href="#"
                           @click.prevent="openMenus.settings = !openMenus.settings"
                           :class="{ 'active': openMenus.settings }">
                            <span>
                                <i class="fas fa-cog me-2"></i>
                                Settings
                            </span>
                            <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': openMenus.settings }"></i>
                        </a>
                    </li>
                    <ul x-show="sidebarOpen && openMenus.settings"
                        x-collapse
                        class="nav nav-pills flex-column small ps-4 gap-1">
                        @can('settings.view')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.settings.*') ? 'active' : '' }}"
                                   href="{{ route('modules.settings.index') }}">
                                    <i class="fas fa-cog me-2"></i>
                                    <span>General Settings</span>
                                </a>
                            </li>
                        @endcan

                        @can('store-settings.view')
                            @if(Module::isActive('store-settings'))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.store-settings.*') ? 'active' : '' }}"
                                       href="{{ route('modules.store-settings.index') }}">
                                        <i class="fas fa-store me-2"></i>
                                        <span>Store Settings</span>
                                    </a>
                                </li>
                            @endif
                        @endcan

                        @can('types.view')
                            @if(Module::isActive('types'))
                                <li class="nav-item">
                                    <a class="nav-link d-flex align-items-center {{ request()->routeIs('modules.types.*') ? 'active' : '' }}"
                                       href="{{ route('modules.types.index') }}">
                                        <i class="fas fa-tags me-2"></i>
                                        <span>Types</span>
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
                                        <span>Users</span>
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
                                        <span>Roles</span>
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
                                        <span>Permissions</span>
                                    </a>
                                </li>
                            @endif
                        @endcan

                        @role('admin')
                            <li class="nav-item">
                                <a class="nav-link d-flex align-items-center {{ request()->routeIs('admin.modules.*') ? 'active' : '' }}"
                                   href="{{ route('admin.modules.index') }}">
                                    <i class="fas fa-cubes me-2"></i>
                                    <span>Module Dictionary</span>
                                </a>
                            </li>
                        @endrole
                    </ul>
                @endif
            </ul>
        </nav>
    </div>
</aside>

<style>
.rotate-180 {
    transform: rotate(180deg);
}

.transition-transform {
    transition: transform 0.2s ease-in-out;
}

/* Submenu styling */
.ps-4 .nav-link {
    font-size: 0.875rem;
    padding-left: 2.5rem !important;
}

/* Parent menu hover effect */
.nav-link:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.1);
}
</style>
