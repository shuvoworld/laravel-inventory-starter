@extends('layouts.adminlte')

@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))

@section('content')
<!-- Stats Cards Row -->
<div class="row">
    @can('users.view')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>{{ __('Users') }}</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('modules.users.index') }}" class="small-box-footer">{{ __('Manage users') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan

    @can('customers.view')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ \App\Modules\Customers\Models\Customer::count() }}</h3>
                <p>Customers</p>
            </div>
            <div class="icon"><i class="fas fa-user-tie"></i></div>
            <a href="{{ route('modules.customers.index') }}" class="small-box-footer">Manage customers <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan

    @can('products.view')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ \App\Modules\Products\Models\Product::count() }}</h3>
                <p>Products</p>
            </div>
            <div class="icon"><i class="fas fa-box"></i></div>
            <a href="{{ route('modules.products.index') }}" class="small-box-footer">Manage products <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan

    @can('sales-order.view')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ \App\Modules\SalesOrder\Models\SalesOrder::count() }}</h3>
                <p>Sales Orders</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <a href="{{ route('modules.sales-order.index') }}" class="small-box-footer">Manage orders <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan
</div>

<!-- Second Row for Purchase Orders -->
<div class="row">
    @can('purchase-order.view')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3>{{ \App\Modules\PurchaseOrder\Models\PurchaseOrder::count() }}</h3>
                <p>Purchase Orders</p>
            </div>
            <div class="icon"><i class="fas fa-truck"></i></div>
            <a href="{{ route('modules.purchase-order.index') }}" class="small-box-footer">Manage purchases <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan
</div>

<!-- Quick Access Cards -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Access</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @can('sales-order.create')
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                <h6 class="card-title">New Sales Order</h6>
                                <a href="{{ route('modules.sales-order.create') }}" class="btn btn-primary btn-sm">Create Order</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('purchase-order.create')
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-secondary">
                            <div class="card-body text-center">
                                <i class="fas fa-truck fa-2x text-secondary mb-2"></i>
                                <h6 class="card-title">New Purchase Order</h6>
                                <a href="{{ route('modules.purchase-order.create') }}" class="btn btn-secondary btn-sm">Create PO</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('customers.create')
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-2x text-success mb-2"></i>
                                <h6 class="card-title">New Customer</h6>
                                <a href="{{ route('modules.customers.create') }}" class="btn btn-success btn-sm">Add Customer</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('products.create')
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <i class="fas fa-box-open fa-2x text-warning mb-2"></i>
                                <h6 class="card-title">New Product</h6>
                                <a href="{{ route('modules.products.create') }}" class="btn btn-warning btn-sm">Add Product</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('stock-movement.view')
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <i class="fas fa-exchange-alt fa-2x text-info mb-2"></i>
                                <h6 class="card-title">Stock Movements</h6>
                                <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-info btn-sm">View Stock</a>
                            </div>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Latest Entries Row -->
<div class="row">
    @can('sales-order.view')
    <div class="col-lg-6">
        <div class="card card-primary card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Latest Sales Orders</h3>
                <div class="card-tools">
                    <a href="{{ route('modules.sales-order.index') }}" class="btn btn-tool">View all</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="dashboard-orders-table" class="table table-striped table-hover table-sm mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('customers.view')
    <div class="col-lg-6">
        <div class="card card-success card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Recent Customers</h3>
                <div class="card-tools">
                    <a href="{{ route('modules.customers.index') }}" class="btn btn-tool">View all</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="dashboard-customers-table" class="table table-striped table-hover table-sm mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Added</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>

<div class="row mt-4">
    @can('products.view')
    <div class="col-lg-6">
        <div class="card card-warning card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Recent Products</h3>
                <div class="card-tools">
                    <a href="{{ route('modules.products.index') }}" class="btn btn-tool">View all</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="dashboard-products-table" class="table table-striped table-hover table-sm mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcan

    @can('stock-movement.view')
    <div class="col-lg-6">
        <div class="card card-info card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Recent Stock Movements</h3>
                <div class="card-tools">
                    <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-tool">View all</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="dashboard-stock-table" class="table table-striped table-hover table-sm mb-0 w-100">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Reference</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>

@role('admin')
<div class="row mt-4">
    <div class="col-lg-12">
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h3 class="card-title">System Administration</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ \App\Models\Module::count() }}</h3>
                                <p>Modules</p>
                            </div>
                            <div class="icon"><i class="fas fa-cubes"></i></div>
                            <a href="{{ route('admin.modules.index') }}" class="small-box-footer">Manage modules <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    @can('users.view')
                    <div class="col-md-3">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ \App\Models\User::count() }}</h3>
                                <p>Users</p>
                            </div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                            <a href="{{ route('modules.users.index') }}" class="small-box-footer">Manage users <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    @endcan
                    @can('roles.view')
                    <div class="col-md-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3>{{ \Spatie\Permission\Models\Role::count() }}</h3>
                                <p>Roles</p>
                            </div>
                            <div class="icon"><i class="fas fa-user-shield"></i></div>
                            <a href="{{ route('modules.roles.index') }}" class="small-box-footer">Manage roles <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    @endcan
                    @can('permissions.view')
                    <div class="col-md-3">
                        <div class="small-box bg-dark">
                            <div class="inner">
                                <h3>{{ \Spatie\Permission\Models\Permission::count() }}</h3>
                                <p>Permissions</p>
                            </div>
                            <div class="icon"><i class="fas fa-key"></i></div>
                            <a href="{{ route('modules.permissions.index') }}" class="small-box-footer">Manage permissions <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endrole

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Sales Orders Table
        const ordersEl = document.querySelector('#dashboard-orders-table');
        if (ordersEl) {
            new DataTable(ordersEl, {
                serverSide: true,
                processing: true,
                ajax: { url: '{{ route('modules.sales-order.data') }}', dataSrc: 'data' },
                columns: [
                    { data: 'order_number' },
                    { data: 'customer_name' },
                    { data: 'status_badge', orderable: false },
                    { data: 'total_amount' },
                    { data: 'order_date' },
                ],
                order: [[0, 'desc']],
                lengthChange: false,
                searching: false,
                pageLength: 5,
                pagingType: 'simple_numbers',
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null }
            });
        }

        // Customers Table
        const customersEl = document.querySelector('#dashboard-customers-table');
        if (customersEl) {
            new DataTable(customersEl, {
                serverSide: true,
                processing: true,
                ajax: { url: '{{ route('modules.customers.data') }}', dataSrc: 'data' },
                columns: [
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'phone' },
                    { data: 'city' },
                    { data: 'created_at' },
                ],
                order: [[4, 'desc']],
                lengthChange: false,
                searching: false,
                pageLength: 5,
                pagingType: 'simple_numbers',
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null }
            });
        }

        // Products Table
        const productsEl = document.querySelector('#dashboard-products-table');
        if (productsEl) {
            new DataTable(productsEl, {
                serverSide: true,
                processing: true,
                ajax: { url: '{{ route('modules.products.data') }}', dataSrc: 'data' },
                columns: [
                    { data: 'name' },
                    { data: 'sku' },
                    {
                        data: 'price',
                        render: function(data, type, row) {
                            return data ? '$' + parseFloat(data).toFixed(2) : 'N/A';
                        }
                    },
                    { data: 'quantity_on_hand' },
                    {
                        data: 'quantity_on_hand',
                        render: function(data, type, row) {
                            const reorderLevel = row.reorder_level || 0;
                            if (data <= reorderLevel) {
                                return '<span class="badge badge-danger">Low Stock</span>';
                            }
                            return '<span class="badge badge-success">In Stock</span>';
                        },
                        orderable: false
                    },
                ],
                order: [[0, 'desc']],
                lengthChange: false,
                searching: false,
                pageLength: 5,
                pagingType: 'simple_numbers',
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null }
            });
        }

        // Stock Movements Table
        const stockEl = document.querySelector('#dashboard-stock-table');
        if (stockEl) {
            new DataTable(stockEl, {
                serverSide: true,
                processing: true,
                ajax: { url: '{{ route('modules.stock-movement.data') }}', dataSrc: 'data' },
                columns: [
                    { data: 'product.name' },
                    {
                        data: 'type',
                        render: function(data, type, row) {
                            const badges = {
                                'in': 'badge-success',
                                'out': 'badge-danger',
                                'adjustment': 'badge-warning'
                            };
                            const class_name = badges[data] || 'badge-secondary';
                            return '<span class="badge ' + class_name + '">' + data.charAt(0).toUpperCase() + data.slice(1) + '</span>';
                        },
                        orderable: false
                    },
                    {
                        data: 'quantity',
                        render: function(data, type, row) {
                            const sign = row.type === 'out' ? '-' : '+';
                            return sign + data;
                        }
                    },
                    {
                        data: 'reference_type',
                        render: function(data, type, row) {
                            return data ? data.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Manual';
                        }
                    },
                    { data: 'created_at' },
                ],
                order: [[4, 'desc']],
                lengthChange: false,
                searching: false,
                pageLength: 5,
                pagingType: 'simple_numbers',
                layout: { topStart: null, topEnd: null, bottomStart: null, bottomEnd: null }
            });
        }
    });
</script>
@endpush