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
<!-- Enhanced Financial Overview Section -->
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
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Monthly Revenue</p>
                        <h3 class="mb-0">{{ formatMoney($currentMonthData['revenue']) }}</h3>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                </div>
                @can('reports.view')
                    <a href="{{ route('modules.reports.profit-loss') }}" class="text-white text-decoration-none small">
                        View Reports <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Operating Expenses</p>
                        <h3 class="mb-0">{{ formatMoney($currentMonthData['operating_expenses']) }}</h3>
                    </div>
                    <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                </div>
                @can('operating-expenses.view')
                    <a href="{{ route('modules.operating-expenses.index') }}" class="text-white text-decoration-none small">
                        Manage Expenses <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 {{ $currentMonthData['net_profit'] >= 0 ? 'bg-primary' : 'bg-danger' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Net Profit</p>
                        <h3 class="mb-0">{{ formatMoney($currentMonthData['net_profit']) }}</h3>
                    </div>
                    <i class="fas fa-chart-line fa-2x opacity-50"></i>
                </div>
                <small class="opacity-75">{{ number_format($currentMonthData['net_profit_margin'], 1) }}% margin</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Sales Orders</p>
                        <h3 class="mb-0">{{ $currentMonthData['orders_count'] }}</h3>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                </div>
                @can('sales-order.view')
                    <a href="{{ route('modules.sales-order.index') }}" class="text-white text-decoration-none small">
                        View Orders <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                @else
                    <small class="opacity-75">Avg: {{ formatMoney($currentMonthData['average_order_value']) }}</small>
                @endcan
            </div>
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
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded">
                        <i class="fas fa-user-tie fa-2x text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-0 small">Customers</p>
                        <h4 class="mb-0">{{ $totalCustomers }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded">
                        <i class="fas fa-box fa-2x text-success"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-0 small">Products</p>
                        <h4 class="mb-0">{{ $totalProducts }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 {{ $lowStockProducts > 0 ? 'bg-warning' : 'bg-secondary' }} bg-opacity-10 p-3 rounded">
                        <i class="fas fa-exclamation-triangle fa-2x {{ $lowStockProducts > 0 ? 'text-warning' : 'text-secondary' }}"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-0 small">Low Stock</p>
                        <h4 class="mb-0">{{ $lowStockProducts }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 {{ $pendingExpenses > 0 ? 'bg-warning' : 'bg-secondary' }} bg-opacity-10 p-3 rounded">
                        <i class="fas fa-clock fa-2x {{ $pendingExpenses > 0 ? 'text-warning' : 'text-secondary' }}"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-muted mb-0 small">Pending Exp.</p>
                        <h4 class="mb-0">{{ formatMoney($pendingExpenses/1000) }}K</h4>
                    </div>
                </div>
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
                        <div class="card shadow-sm bg-success text-white h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-cash-register fa-2x mb-2"></i>
                                <h6 class="mb-2">Point of Sale</h6>
                                <a href="{{ route('pos.index') }}" target="_blank" class="btn btn-light btn-sm">
                                    <i class="fas fa-external-link-alt me-1"></i> Open POS
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('sales-order.create')
                    <div class="col-md-2 col-sm-6 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-plus-circle fa-2x text-primary mb-2"></i>
                                <h6 class="mb-2">New Sales Order</h6>
                                <a href="{{ route('modules.sales-order.create') }}" class="btn btn-primary btn-sm">Create Order</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('purchase-order.create')
                    <div class="col-md-2 col-sm-6 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-truck fa-2x text-secondary mb-2"></i>
                                <h6 class="mb-2">New Purchase Order</h6>
                                <a href="{{ route('modules.purchase-order.create') }}" class="btn btn-secondary btn-sm">Create PO</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('customers.create')
                    <div class="col-md-2 col-sm-6 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-user-plus fa-2x text-success mb-2"></i>
                                <h6 class="mb-2">New Customer</h6>
                                <a href="{{ route('modules.customers.create') }}" class="btn btn-success btn-sm">Add Customer</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('products.create')
                    <div class="col-md-2 col-sm-6 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-box-open fa-2x text-warning mb-2"></i>
                                <h6 class="mb-2">New Product</h6>
                                <a href="{{ route('modules.products.create') }}" class="btn btn-warning btn-sm">Add Product</a>
                            </div>
                        </div>
                    </div>
                    @endcan

                    @can('stock-movement.view')
                    <div class="col-md-2 col-sm-6 mb-3">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-exchange-alt fa-2x text-info mb-2"></i>
                                <h6 class="mb-2">Stock Movements</h6>
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
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>
                    Recent Sales Orders
                </h5>
                <a href="{{ route('modules.sales-order.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                @if($recentSalesOrders->count() > 0)
                    <div class="table-responsive mb-0">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Order #</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSalesOrders as $order)
                                    <tr class="align-middle">
                                        <td>
                                            <span class="badge bg-primary text-white">{{ $order->order_number }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $order->customer->name ?? 'Walk-in' }}</strong>
                                                @if($order->customer->email)
                                                    <br><small class="text-muted">{{ $order->customer->email }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">{{ formatMoney($order->total_amount) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column align-items-start">
                                                <small class="text-muted mb-1">{{ $order->created_at->format('M j, Y') }}</small>
                                                @switch($order->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pending</span>
                                                        @break
                                                    @case('confirmed')
                                                        <span class="badge bg-info">Confirmed</span>
                                                        @break
                                                    @case('delivered')
                                                        <span class="badge bg-success">Delivered</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Cancelled</span>
                                                        @break
                                                    @case('refunded')
                                                        <span class="badge bg-secondary">Refunded</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $order->status }}</span>
                                                @endswitch
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-5 text-center text-muted">
                        <i class="fas fa-shopping-cart fa-2x mb-3 opacity-50"></i>
                        <p class="mb-0">No recent sales orders</p>
                        <a href="{{ route('modules.sales-order.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i> Create First Order
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endcan

    @can('operating-expenses.view')
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice me-2"></i>
                    Recent Expenses
                </h5>
                <a href="{{ route('modules.operating-expenses.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
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
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Customers</h5>
                <a href="{{ route('modules.customers.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
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

<!-- Recent Transactions Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-receipt me-2"></i>
                    Recent Transactions (Last 10)
                </h5>
            </div>
            <div class="card-body p-0">
                @if(isset($recentTransactions) && count($recentTransactions) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Party</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentTransactions as $transaction)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $transaction['color'] }}">
                                                <i class="{{ $transaction['icon'] }} me-1"></i>
                                                {{ $transaction['type_display'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $transaction['reference'] }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $transaction['party'] }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <span class="text-muted">{{ $transaction['date'] }}</span>
                                                <br>
                                                <span class="text-muted">{{ $transaction['time'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold">
                                                @if($transaction['type'] === 'sale')
                                                    <span class="text-success">{{ formatMoney($transaction['amount']) }}</span>
                                                @elseif($transaction['type'] === 'purchase')
                                                    <span class="text-warning">{{ formatMoney($transaction['amount']) }}</span>
                                                @else
                                                    <span class="text-danger">{{ formatMoney($transaction['amount']) }}</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{
                                                $transaction['status'] === 'completed' || $transaction['status'] === 'delivered' ? 'success' :
                                                ($transaction['status'] === 'pending' ? 'warning' : 'secondary')
                                            }}">
                                                {{ ucfirst($transaction['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 text-center border-top">
                        <a href="#" class="text-primary">
                            View all transactions <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                        <p>No recent transactions found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- 12-Month Financial Trends Chart -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    12-Month Financial Trends
                </h5>
            </div>
            <div class="card-body">
                @if(isset($monthlyFinancialData) && count($monthlyFinancialData) > 0)
                    <div class="chart-container" style="position: relative; height: 400px;">
                        <canvas id="monthlyFinancialChart"></canvas>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-success">{{ formatMoney(array_sum(array_column($monthlyFinancialData, 'sales'))) }}</h4>
                                <p class="text-muted mb-0">Total Sales (12 months)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-warning">{{ formatMoney(array_sum(array_column($monthlyFinancialData, 'purchases'))) }}</h4>
                                <p class="text-muted mb-0">Total Purchases (12 months)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center">
                                <h4 class="text-{{ array_sum(array_column($monthlyFinancialData, 'profit')) >= 0 ? 'info' : 'danger' }}">
                                    {{ formatMoney(array_sum(array_column($monthlyFinancialData, 'profit'))) }}
                                </h4>
                                <p class="text-muted mb-0">Total Profit (12 months)</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-line fa-3x mb-3 opacity-25"></i>
                        <p>No financial data available for the past 12 months</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    @can('products.view')
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Products</h5>
                <a href="{{ route('modules.products.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="card-body p-0">
                @if($recentProducts && $recentProducts->count() > 0)
                    <div class="table-responsive">
                        <table id="dashboard-products-table" class="table table-hover align-middle">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="border-0">Product</th>
                                    <th class="border-0">SKU</th>
                                    <th class="text-center border-0">Price</th>
                                    <th class="text-center border-0">Stock</th>
                                    <th class="text-center border-0">Status</th>
                                    <th class="text-end border-0">Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentProducts as $product)
                                    <tr>
                                        <td class="text-start">
                                            <div class="d-flex align-items-center">
                                                @if($product->image)
                                                    <img src="{{ asset('storage/' . $product->image) }}"
                                                         alt="{{ $product->name }}"
                                                         class="rounded-circle me-2"
                                                         style="width: 32px; height: 32px; object-fit: cover;">
                                                @endif
                                                <div>
                                                    <strong>{{ Str::limit($product->name, 35) }}</strong>
                                                    @if($product->sku)
                                                        <small class="text-muted d-block">SKU: {{ $product->sku }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center"><span class="badge bg-light text-dark">{{ $product->sku ?? 'N/A' }}</span></td>
                                        <td class="text-center fw-bold">{{ formatMoney($product->target_price ?? $product->price) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $product->quantity_on_hand > 10 ? 'bg-danger' : ($product->quantity_on_hand > 5 ? 'bg-warning' : 'bg-success') }} text-white">
                                                {{ $product->quantity_on_hand }}
                                            </span>
                                            <small class="d-block text-muted">
                                                {{ $product->quantity_on_hand > 10 ? 'Critical Low' : ($product->quantity_on_hand > 5 ? 'Low Stock' : 'Good Stock') }}
                                            </small>
                                        </td>
                                        <td class="text-center">{{ $product->status === 'active' ? 'Available' : 'Inactive' }}</td>
                                        <td class="text-end fw-bold text-success">{{ formatMoney(($product->target_price ?? $product->price) * $product->quantity_on_hand) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-cube fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No recent products to display</p>
                        @can('products.create')
                            <a href="{{ route('modules.products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>
    </div>
    @can('products.view')
        </div>
    </div>
    @endcan

    @can('stock-movement.view')
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Stock Movements</h5>
                <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
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
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">System Administration</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="mb-1 small opacity-75">Modules</p>
                                        <h3 class="mb-0">{{ \App\Models\Module::count() }}</h3>
                                    </div>
                                    <i class="fas fa-cubes fa-2x opacity-50"></i>
                                </div>
                                <a href="{{ route('admin.modules.index') }}" class="text-white text-decoration-none small">
                                    Manage modules <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @can('users.view')
                    <div class="col-md-3">
                        <div class="card border-0 bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="mb-1 small opacity-75">Users</p>
                                        <h3 class="mb-0">{{ \App\Models\User::count() }}</h3>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                                <a href="{{ route('modules.users.index') }}" class="text-white text-decoration-none small">
                                    Manage users <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcan
                    @can('roles.view')
                    <div class="col-md-3">
                        <div class="card border-0 bg-secondary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="mb-1 small opacity-75">Roles</p>
                                        <h3 class="mb-0">{{ \Spatie\Permission\Models\Role::count() }}</h3>
                                    </div>
                                    <i class="fas fa-user-shield fa-2x opacity-50"></i>
                                </div>
                                <a href="{{ route('modules.roles.index') }}" class="text-white text-decoration-none small">
                                    Manage roles <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endcan
                    @can('permissions.view')
                    <div class="col-md-3">
                        <div class="card border-0 bg-dark text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="mb-1 small opacity-75">Permissions</p>
                                        <h3 class="mb-0">{{ \Spatie\Permission\Models\Permission::count() }}</h3>
                                    </div>
                                    <i class="fas fa-key fa-2x opacity-50"></i>
                                </div>
                                <a href="{{ route('modules.permissions.index') }}" class="text-white text-decoration-none small">
                                    Manage permissions <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
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

        // 12-Month Financial Trends Chart
        const monthlyFinancialCtx = document.getElementById('monthlyFinancialChart');
        if (monthlyFinancialCtx && @isset($monthlyFinancialData)) {
            const monthlyData = @json($monthlyFinancialData);

            new Chart(monthlyFinancialCtx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(item => item.month),
                    datasets: [
                        {
                            label: 'Sales',
                            data: monthlyData.map(item => item.sales),
                            borderColor: 'rgb(40, 167, 69)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(40, 167, 69)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Purchases',
                            data: monthlyData.map(item => item.purchases),
                            borderColor: 'rgb(255, 193, 7)',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(255, 193, 7)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Profit',
                            data: monthlyData.map(item => item.profit),
                            borderColor: 'rgb(13, 202, 240)',
                            backgroundColor: 'rgba(13, 202, 240, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(13, 202, 240)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-US', {
                                            style: 'currency',
                                            currency: 'USD'
                                        }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            display: true,
                            position: 'left',
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });

        // 12-Month Financial Trends Chart
        const monthlyFinancialCtx = document.getElementById('monthlyFinancialChart');
        if (monthlyFinancialCtx && @isset($monthlyFinancialData)) {
            const monthlyData = @json($monthlyFinancialData);

            new Chart(monthlyFinancialCtx, {
                type: 'line',
                data: {
                    labels: monthlyData.map(item => item.month),
                    datasets: [
                        {
                            label: 'Sales',
                            data: monthlyData.map(item => item.sales),
                            borderColor: 'rgb(40, 167, 69)',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(40, 167, 69)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Purchases',
                            data: monthlyData.map(item => item.purchases),
                            borderColor: 'rgb(255, 193, 7)',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(255, 193, 7)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        },
                        {
                            label: 'Profit',
                            data: monthlyData.map(item => item.profit),
                            borderColor: 'rgb(13, 202, 240)',
                            backgroundColor: 'rgba(13, 202, 240, 0.1)',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(13, 202, 240)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12
                                }
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(255, 255, 255, 0.2)',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-US', {
                                            style: 'currency',
                                            currency: 'USD'
                                        }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            display: true,
                            position: 'left',
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        }
    });
</script>
@endpush
