@extends('layouts.app')

@section('title', 'Superadmin Dashboard')
@section('page-title', 'Superadmin Dashboard - System-Wide Analytics')

@php
use App\Modules\StoreSettings\Models\StoreSetting;

function formatMoney($amount) {
    return StoreSetting::formatCurrency($amount);
}
@endphp

@section('content')
<!-- System-Wide Overview Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-gradient-primary text-white">
            <div class="card-header border-0">
                <h3 class="card-title">
                    <i class="fas fa-globe mr-1"></i>
                    System-Wide Performance - {{ now()->format('F Y') }}
                </h3>
                <div class="card-tools">
                    <span class="badge bg-light text-dark">
                        <i class="fas fa-store mr-1"></i> {{ $totalStores }} Total Stores | {{ $activeStores }} Active
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="description-block border-right">
                            <span class="description-percentage text-{{ $systemRevenueGrowth >= 0 ? 'success' : 'danger' }}">
                                <i class="fas fa-caret-{{ $systemRevenueGrowth >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($systemRevenueGrowth), 1) }}%
                            </span>
                            <h5 class="description-header text-white">{{ formatMoney($systemWideCurrentMonth['revenue']) }}</h5>
                            <span class="description-text text-white-50">TOTAL SYSTEM REVENUE</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="description-block border-right">
                            <span class="description-percentage text-warning">
                                <i class="fas fa-shopping-cart"></i> {{ $systemWideCurrentMonth['orders_count'] }} Orders
                            </span>
                            <h5 class="description-header text-white">{{ formatMoney($systemWideCurrentMonth['average_order_value']) }}</h5>
                            <span class="description-text text-white-50">AVG ORDER VALUE</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="description-block border-right">
                            <span class="description-percentage {{ $systemWideCurrentMonth['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($systemWideCurrentMonth['gross_profit_margin'], 1) }}%
                            </span>
                            <h5 class="description-header text-white">{{ formatMoney($systemWideCurrentMonth['gross_profit']) }}</h5>
                            <span class="description-text text-white-50">GROSS PROFIT</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="description-block">
                            <span class="description-percentage {{ $systemProfitGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-caret-{{ $systemProfitGrowth >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($systemProfitGrowth), 1) }}%
                            </span>
                            <h5 class="description-header {{ $systemWideCurrentMonth['net_profit'] >= 0 ? 'text-white' : 'text-warning' }}">
                                {{ formatMoney($systemWideCurrentMonth['net_profit']) }}
                            </h5>
                            <span class="description-text text-white-50">NET PROFIT</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Total Products</p>
                        <h3 class="mb-0">{{ number_format($totalProducts) }}</h3>
                    </div>
                    <i class="fas fa-box fa-2x opacity-50"></i>
                </div>
                <small class="opacity-75">Across all stores</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Total Customers</p>
                        <h3 class="mb-0">{{ number_format($totalCustomers) }}</h3>
                    </div>
                    <i class="fas fa-user-tie fa-2x opacity-50"></i>
                </div>
                <small class="opacity-75">System-wide</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Active Users</p>
                        <h3 class="mb-0">{{ $totalUsers }}</h3>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
                <small class="opacity-75">All stores combined</small>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6">
        <div class="card border-0 shadow-sm h-100 {{ $lowStockCount > 0 ? 'bg-warning' : 'bg-secondary' }} text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <p class="mb-1 small opacity-75">Low Stock Items</p>
                        <h3 class="mb-0">{{ $lowStockCount }}</h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                </div>
                <small class="opacity-75">{{ $outOfStockCount }} out of stock</small>
            </div>
        </div>
    </div>
</div>

<!-- System-Wide Trend Chart -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-area mr-1"></i>
                    30-Day System-Wide Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="systemTrendChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy mr-1"></i>
                    Top Performing Stores
                </h5>
            </div>
            <div class="card-body p-0">
                @if($topStores->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($topStores as $index => $store)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'info') }} me-2">
                                        #{{ $index + 1 }}
                                    </span>
                                    <strong>{{ $store['store']->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $store['orders_count'] }} orders</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success">{{ formatMoney($store['revenue']) }}</div>
                                    <small class="text-muted">{{ formatMoney($store['profit']) }} profit</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-3 text-center text-muted">
                        No store data available
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Store-by-Store Performance Comparison -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-store mr-1"></i>
                    Store Performance Comparison - {{ now()->format('F Y') }}
                </h5>
            </div>
            <div class="card-body p-0">
                @if(count($storePerformance) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Store</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Expenses</th>
                                    <th class="text-end">Profit</th>
                                    <th class="text-center">Products</th>
                                    <th class="text-center">Low Stock</th>
                                    <th class="text-end">Avg Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($storePerformance as $performance)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $performance['store']->name }}</strong>
                                                @if(!$performance['store']->is_active)
                                                    <span class="badge bg-secondary ms-1">Inactive</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($performance['store']->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-{{ $performance['revenue'] > 0 ? 'success' : 'muted' }}">
                                            {{ formatMoney($performance['revenue']) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $performance['orders_count'] }}</span>
                                        </td>
                                        <td class="text-end text-warning">
                                            {{ formatMoney($performance['expenses']) }}
                                        </td>
                                        <td class="text-end fw-bold text-{{ $performance['profit'] >= 0 ? 'success' : 'danger' }}">
                                            {{ formatMoney($performance['profit']) }}
                                        </td>
                                        <td class="text-center">
                                            {{ $performance['products_count'] }}
                                        </td>
                                        <td class="text-center">
                                            @if($performance['low_stock_count'] > 0)
                                                <span class="badge bg-warning">{{ $performance['low_stock_count'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            {{ formatMoney($performance['avg_order_value']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="2">TOTAL</td>
                                    <td class="text-end text-success">{{ formatMoney(collect($storePerformance)->sum('revenue')) }}</td>
                                    <td class="text-center">{{ collect($storePerformance)->sum('orders_count') }}</td>
                                    <td class="text-end text-warning">{{ formatMoney(collect($storePerformance)->sum('expenses')) }}</td>
                                    <td class="text-end text-{{ collect($storePerformance)->sum('profit') >= 0 ? 'success' : 'danger' }}">
                                        {{ formatMoney(collect($storePerformance)->sum('profit')) }}
                                    </td>
                                    <td class="text-center">{{ collect($storePerformance)->sum('products_count') }}</td>
                                    <td class="text-center">{{ collect($storePerformance)->sum('low_stock_count') }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-store fa-3x mb-3 opacity-25"></i>
                        <p>No store data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Store Health Metrics -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-heartbeat mr-1"></i>
                    Store Health Metrics
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($storeHealthMetrics as $metric)
                        <div class="col-md-4 col-lg-3">
                            <div class="card border-0 h-100 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $metric['store']->name }}</h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3">
                                            <div class="position-relative" style="width: 60px; height: 60px;">
                                                <svg viewBox="0 0 36 36" class="circular-chart">
                                                    <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#eee" stroke-width="3"/>
                                                    <path class="circle" stroke-dasharray="{{ $metric['health_score'] }}, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="{{ $metric['status'] === 'excellent' ? '#28a745' : ($metric['status'] === 'good' ? '#17a2b8' : ($metric['status'] === 'warning' ? '#ffc107' : '#dc3545')) }}" stroke-width="3"/>
                                                </svg>
                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                    <small class="fw-bold">{{ $metric['health_score'] }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="badge bg-{{ $metric['status'] === 'excellent' ? 'success' : ($metric['status'] === 'good' ? 'info' : ($metric['status'] === 'warning' ? 'warning' : 'danger')) }}">
                                                {{ ucfirst($metric['status']) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Revenue:</span>
                                            <strong>{{ formatMoney($metric['revenue']) }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Users:</span>
                                            <strong>{{ $metric['active_users'] }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Products:</span>
                                            <strong>{{ $metric['products'] }}</strong>
                                        </div>
                                        @if($metric['low_stock'] > 0)
                                            <div class="d-flex justify-content-between">
                                                <span class="text-warning">Low Stock:</span>
                                                <strong class="text-warning">{{ $metric['low_stock'] }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Comparison Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line mr-1"></i>
                    6-Month Performance Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyComparisonChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent System-Wide Transactions -->
<div class="row">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Recent Sales Orders (All Stores)
                </h5>
            </div>
            <div class="card-body p-0">
                @if($recentSalesOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Store</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentSalesOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">{{ $order->order_number }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $order->store->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $order->customer->name ?? 'Walk-in' }}</strong>
                                        </td>
                                        <td class="fw-bold text-success">
                                            {{ formatMoney($order->total_amount) }}
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $order->created_at->format('M j, Y') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        No recent sales orders
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-truck mr-1"></i>
                    Recent Purchase Orders (All Stores)
                </h5>
            </div>
            <div class="card-body p-0">
                @if($recentPurchaseOrders->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>PO #</th>
                                    <th>Store</th>
                                    <th>Supplier</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPurchaseOrders as $order)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ $order->order_number }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $order->store->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $order->supplier->name ?? 'N/A' }}</strong>
                                        </td>
                                        <td class="fw-bold text-warning">
                                            {{ formatMoney($order->total_amount) }}
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $order->created_at->format('M j, Y') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        No recent purchase orders
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // System-Wide Trend Chart
    const trendCtx = document.getElementById('systemTrendChart');
    if (trendCtx) {
        const trendData = @json($systemWideTrend);

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
                        fill: true
                    },
                    {
                        label: 'Expenses',
                        data: trendData.map(item => item.expenses),
                        borderColor: 'rgb(255, 193, 7)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4,
                        fill: true
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
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                            }
                        }
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
                }
            }
        });
    }

    // Monthly Comparison Chart
    const comparisonCtx = document.getElementById('monthlyComparisonChart');
    if (comparisonCtx) {
        const comparisonData = @json($monthlyComparison);

        new Chart(comparisonCtx, {
            type: 'bar',
            data: {
                labels: comparisonData.map(item => item.month),
                datasets: [
                    {
                        label: 'Revenue',
                        data: comparisonData.map(item => item.revenue),
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: 'rgb(40, 167, 69)',
                        borderWidth: 1
                    },
                    {
                        label: 'Expenses',
                        data: comparisonData.map(item => item.expenses),
                        backgroundColor: 'rgba(255, 193, 7, 0.8)',
                        borderColor: 'rgb(255, 193, 7)',
                        borderWidth: 1
                    },
                    {
                        label: 'Profit',
                        data: comparisonData.map(item => item.profit),
                        backgroundColor: 'rgba(0, 123, 255, 0.8)',
                        borderColor: 'rgb(0, 123, 255)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                            }
                        }
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
                }
            }
        });
    }
});
</script>

<style>
.circular-chart {
    display: block;
    max-width: 100%;
    max-height: 100%;
}

.circle {
    stroke-linecap: round;
    animation: progress 1s ease-out forwards;
}

@keyframes progress {
    0% {
        stroke-dasharray: 0 100;
    }
}
</style>
@endpush
