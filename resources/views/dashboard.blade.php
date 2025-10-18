@extends('layouts.app')

@section('title', __('common.dashboard'))
@section('page-title', __('common.dashboard'))

@php
use App\Modules\StoreSettings\Models\StoreSetting;

function formatMoney($amount) {
    return StoreSetting::formatCurrency($amount);
}
@endphp

@section('content')
<!-- Financial Overview Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary">
            <div class="card-header border-0">
                <h3 class="card-title text-white">
                    <i class="fas fa-chart-line mr-1"></i>
                    Profit & Loss Summary - {{ now()->format('F Y') }}
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#financialModal">
                        <i class="fas fa-chart-bar"></i> View Details
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="description-block border-right">
                            <span class="description-percentage text-success">
                                <i class="fas fa-caret-{{ $revenueGrowth >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($revenueGrowth), 1) }}%
                            </span>
                            <h5 class="description-header text-white">{{ formatMoney($currentMonthData['revenue']) }}</h5>
                            <span class="description-text text-white-50">TOTAL REVENUE</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="description-block border-right">
                            <span class="description-percentage text-warning">
                                <i class="fas fa-caret-{{ $expenseGrowth >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($expenseGrowth), 1) }}%
                            </span>
                            <h5 class="description-header text-white">{{ formatMoney($currentMonthData['cogs'] + $currentMonthData['operating_expenses']) }}</h5>
                            <span class="description-text text-white-50">TOTAL EXPENSES</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="description-block border-right">
                            <span class="description-percentage {{ $currentMonthData['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($currentMonthData['gross_profit_margin'], 1) }}%
                            </span>
                            <h5 class="description-header text-white">{{ formatMoney($currentMonthData['gross_profit']) }}</h5>
                            <span class="description-text text-white-50">GROSS PROFIT</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="description-block">
                            <span class="description-percentage {{ $profitGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-caret-{{ $profitGrowth >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($profitGrowth), 1) }}%
                            </span>
                            <h5 class="description-header {{ $currentMonthData['net_profit'] >= 0 ? 'text-white' : 'text-warning' }}">
                                {{ formatMoney($currentMonthData['net_profit']) }}
                            </h5>
                            <span class="description-text text-white-50">NET PROFIT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Key Metrics Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ formatMoney($currentMonthData['revenue']) }}</h3>
                <p>Monthly Revenue</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            @can('reports.view')
                <a href="{{ route('modules.reports.profit-loss') }}" class="small-box-footer">View Reports <i class="fas fa-arrow-circle-right"></i></a>
            @else
                <div class="small-box-footer">&nbsp;</div>
            @endcan
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ formatMoney($currentMonthData['operating_expenses']) }}</h3>
                <p>Operating Expenses</p>
            </div>
            <div class="icon"><i class="fas fa-file-invoice"></i></div>
            @can('operating-expenses.view')
                <a href="{{ route('modules.operating-expenses.index') }}" class="small-box-footer">Manage Expenses <i class="fas fa-arrow-circle-right"></i></a>
            @else
                <div class="small-box-footer">&nbsp;</div>
            @endcan
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box {{ $currentMonthData['net_profit'] >= 0 ? 'bg-primary' : 'bg-danger' }}">
            <div class="inner">
                <h3>{{ formatMoney($currentMonthData['net_profit']) }}</h3>
                <p>Net Profit</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
            <div class="small-box-footer">{{ number_format($currentMonthData['net_profit_margin'], 1) }}% margin</div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $currentMonthData['orders_count'] }}</h3>
                <p>Sales Orders</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            @can('sales-order.view')
                <a href="{{ route('modules.sales-order.index') }}" class="small-box-footer">View Orders <i class="fas fa-arrow-circle-right"></i></a>
            @else
                <div class="small-box-footer">Avg: {{ formatMoney($currentMonthData['average_order_value']) }}</div>
            @endcan
        </div>
    </div>
</div>

<!-- Financial Trend Chart -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-1"></i>
                    30-Day Financial Trend
                </h3>
                <div class="card-tools">
                    @can('reports.view')
                        <a href="{{ route('modules.reports.profit-loss') }}" class="btn btn-tool">
                            <i class="fas fa-external-link-alt"></i> Full Report
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <canvas id="trendChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Alerts & Insights
                </h3>
            </div>
            <div class="card-body">
                @if($lowStockProducts > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-box-open mr-2"></i>
                        <strong>{{ $lowStockProducts }} products</strong> are running low on stock.
                        @can('products.view')
                            <a href="{{ route('modules.products.index') }}" class="alert-link">View Products</a>
                        @endcan
                    </div>
                @endif

                @if($pendingExpenses > 0)
                    <div class="alert alert-info">
                        <i class="fas fa-clock mr-2"></i>
                        <strong>{{ formatMoney($pendingExpenses) }}</strong> in pending expenses.
                        @can('operating-expenses.view')
                            <a href="{{ route('modules.operating-expenses.index') }}" class="alert-link">Review Expenses</a>
                        @endcan
                    </div>
                @endif

                @if($currentMonthData['net_profit'] < 0)
                    <div class="alert alert-danger">
                        <i class="fas fa-chart-line-down mr-2"></i>
                        <strong>Negative profit</strong> this month. Review expenses and pricing.
                    </div>
                @elseif($currentMonthData['net_profit_margin'] < 10)
                    <div class="alert alert-warning">
                        <i class="fas fa-percentage mr-2"></i>
                        <strong>Low profit margin</strong> ({{ number_format($currentMonthData['net_profit_margin'], 1) }}%). Consider optimization.
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="fas fa-thumbs-up mr-2"></i>
                        <strong>Healthy profit margin</strong> of {{ number_format($currentMonthData['net_profit_margin'], 1) }}%.
                    </div>
                @endif

                <div class="text-center mt-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Based on current month performance
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Business Stats Overview -->
<div class="row mb-4">
    <div class="col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-user-tie"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Customers</span>
                <span class="info-box-number">{{ $totalCustomers }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-box"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Products</span>
                <span class="info-box-number">{{ $totalProducts }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon {{ $lowStockProducts > 0 ? 'bg-warning' : 'bg-secondary' }}">
                <i class="fas fa-exclamation-triangle"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Low Stock</span>
                <span class="info-box-number">{{ $lowStockProducts }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="info-box">
            <span class="info-box-icon {{ $pendingExpenses > 0 ? 'bg-warning' : 'bg-secondary' }}">
                <i class="fas fa-clock"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Pending Exp.</span>
                <span class="info-box-number">{{ formatMoney($pendingExpenses/1000) }}K</span>
            </div>
        </div>
    </div>
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
                    <div class="col-md-2 col-sm-6 mb-3">
                        <div class="card bg-gradient-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-cash-register fa-2x mb-2"></i>
                                <h6 class="card-title text-white">Point of Sale</h6>
                                <a href="{{ route('pos.index') }}" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i> Open POS
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('sales-order.create')
                    <div class="col-md-2 col-sm-6 mb-3">
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
                    <div class="col-md-2 col-sm-6 mb-3">
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
                    <div class="col-md-2 col-sm-6 mb-3">
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
                    <div class="col-md-2 col-sm-6 mb-3">
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
                    <div class="col-md-2 col-sm-6 mb-3">
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

<!-- Recent Financial Activities -->
<div class="row">
    @can('sales-order.view')
    <div class="col-lg-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Recent Sales Orders
                </h3>
                <div class="card-tools">
                    <a href="{{ route('modules.sales-order.index') }}" class="btn btn-tool">View all</a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentSalesOrders->count() > 0)
                    @foreach($recentSalesOrders as $order)
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div>
                                <strong>{{ $order->order_number }}</strong><br>
                                <small class="text-muted">{{ $order->customer->name ?? 'N/A' }}</small>
                            </div>
                            <div class="text-right">
                                <div class="text-success font-weight-bold">{{ formatMoney($order->total_amount) }}</div>
                                <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-3 text-center text-muted">
                        No recent sales orders
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endcan

    @can('operating-expenses.view')
    <div class="col-lg-6">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-invoice mr-1"></i>
                    Recent Expenses
                </h3>
                <div class="card-tools">
                    <a href="{{ route('modules.operating-expenses.index') }}" class="btn btn-tool">View all</a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($recentExpenses->count() > 0)
                    @foreach($recentExpenses as $expense)
                        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                            <div>
                                <strong>{{ $expense->category_label }}</strong><br>
                                <small class="text-muted">{{ $expense->description }}</small>
                            </div>
                            <div class="text-right">
                                <div class="text-warning font-weight-bold">{{ formatMoney($expense->amount) }}</div>
                                <small class="text-muted">{{ $expense->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-3 text-center text-muted">
                        No recent expenses
                    </div>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="col-lg-6">
        <div class="card card-success card-outline">
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

<!-- Financial Details Modal -->
<div class="modal fade" id="financialModal" tabindex="-1" role="dialog" aria-labelledby="financialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="financialModalLabel">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Financial Summary - {{ now()->format('F Y') }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Current Month</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td>Revenue:</td>
                                <td class="text-right text-success">{{ formatMoney($currentMonthData['revenue']) }}</td>
                            </tr>
                            <tr>
                                <td>COGS:</td>
                                <td class="text-right text-danger">{{ formatMoney($currentMonthData['cogs']) }}</td>
                            </tr>
                            <tr>
                                <td>Operating Expenses:</td>
                                <td class="text-right text-warning">{{ formatMoney($currentMonthData['operating_expenses']) }}</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td>Gross Profit:</td>
                                <td class="text-right text-primary">{{ formatMoney($currentMonthData['gross_profit']) }}</td>
                            </tr>
                            <tr class="font-weight-bold border-top">
                                <td>Net Profit:</td>
                                <td class="text-right {{ $currentMonthData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatMoney($currentMonthData['net_profit']) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Previous Month (Comparison)</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td>Revenue:</td>
                                <td class="text-right">{{ formatMoney($previousMonthData['revenue']) }}</td>
                            </tr>
                            <tr>
                                <td>COGS:</td>
                                <td class="text-right">{{ formatMoney($previousMonthData['cogs']) }}</td>
                            </tr>
                            <tr>
                                <td>Operating Expenses:</td>
                                <td class="text-right">{{ formatMoney($previousMonthData['operating_expenses']) }}</td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td>Gross Profit:</td>
                                <td class="text-right">{{ formatMoney($previousMonthData['gross_profit']) }}</td>
                            </tr>
                            <tr class="font-weight-bold border-top">
                                <td>Net Profit:</td>
                                <td class="text-right {{ $previousMonthData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatMoney($previousMonthData['net_profit']) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @can('reports.view')
                    <a href="{{ route('modules.reports.profit-loss') }}" class="btn btn-primary">
                        <i class="fas fa-chart-line mr-1"></i> View Full Report
                    </a>
                @endcan
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Financial Trend Chart
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            const trendData = @json($trendData);

            new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.map(item => item.date),
                    datasets: [
                        {
                            label: 'Revenue',
                            data: trendData.map(item => item.revenue),
                            borderColor: 'rgb(40, 167, 69)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'Total Expenses',
                            data: trendData.map(item => item.expenses),
                            borderColor: 'rgb(255, 193, 7)',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4,
                            fill: false
                        },
                        {
                            label: 'Net Profit',
                            data: trendData.map(item => item.net_profit),
                            borderColor: 'rgb(0, 123, 255)',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

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
                            if (data === null || data === undefined || data === '') {
                                return 'N/A';
                            }
                            const price = parseFloat(data);
                            return isNaN(price) ? 'N/A' : '$' + price.toFixed(2);
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
