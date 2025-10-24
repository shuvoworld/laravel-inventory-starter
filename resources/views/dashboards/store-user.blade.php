@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sales Dashboard</h1>
            <p class="text-muted mb-0">{{ $companyInfo['name'] ?? 'Store Dashboard' }}</p>
        </div>
        <div class="text-end">
            <small class="text-muted">{{ now()->format('l, F j, Y') }}</small>
        </div>
    </div>

    <!-- Today's Sales Overview -->
    <div class="row g-3 mb-4">
        <!-- Today's Revenue -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card-primary h-100">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">Today's Revenue</p>
                            <h2 class="mb-0 fw-bold">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($todaysRevenue, 2) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded">
                            <i class="fas fa-dollar-sign fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75"><i class="fas fa-clock me-1"></i>Updated live</small>
                </div>
            </div>
        </div>

        <!-- Today's Orders -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card-accent h-100">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">Today's Orders</p>
                            <h2 class="mb-0 fw-bold">{{ number_format($todaysOrdersCount) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75"><i class="fas fa-calendar-day me-1"></i>{{ now()->format('M d, Y') }}</small>
                </div>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card-secondary h-100">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">Avg Order Value</p>
                            <h2 class="mb-0 fw-bold">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($avgOrderValue, 2) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded">
                            <i class="fas fa-chart-bar fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75"><i class="fas fa-calculator me-1"></i>Per order today</small>
                </div>
            </div>
        </div>

        <!-- This Week's Sales -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm metric-card-dark h-100">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 opacity-75 small">This Week's Sales</p>
                            <h2 class="mb-0 fw-bold">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($weekSales, 2) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-25 p-2 rounded">
                            <i class="fas fa-calendar-week fa-lg"></i>
                        </div>
                    </div>
                    <small class="opacity-75"><i class="fas fa-arrow-trend-up me-1"></i>Week to date</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions</h5>
                    <div class="row g-2">
                        @can('sales-order.create')
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.sales-order.create') }}" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-plus-circle me-2"></i>New Sales Order
                            </a>
                        </div>
                        @endcan

                        @can('customers.view')
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.customers.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-users me-2"></i>View Customers
                            </a>
                        </div>
                        @endcan

                        @can('products.view')
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.products.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-boxes me-2"></i>Browse Products
                            </a>
                        </div>
                        @endcan

                        @can('sales-order.view')
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.sales-order.index') }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-list me-2"></i>All Orders
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders & Quick Stats -->
    <div class="row g-3">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-clock-rotate-left text-primary me-2"></i>Today's Orders</h5>
                    <span class="badge bg-primary">{{ $recentOrders->count() }} orders</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Time</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->customer->name ?? 'Walk-in Customer' }}</td>
                                    <td><small class="text-muted">{{ $order->created_at->format('h:i A') }}</small></td>
                                    <td><strong class="text-primary">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($order->total_amount, 2) }}</strong></td>
                                    <td>
                                        @if($order->status === 'delivered')
                                            <span class="badge bg-success">Delivered</span>
                                        @elseif($order->status === 'shipped')
                                            <span class="badge bg-info">Shipped</span>
                                        @elseif($order->status === 'processing')
                                            <span class="badge bg-warning">Processing</span>
                                        @elseif($order->status === 'confirmed')
                                            <span class="badge bg-primary">Confirmed</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @can('sales-order.view')
                                        <a href="{{ route('modules.sales-order.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted mb-0">No orders yet today</p>
                                        @can('sales-order.create')
                                        <a href="{{ route('modules.sales-order.create') }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="fas fa-plus me-1"></i>Create First Order
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($recentOrders->count() > 0)
                <div class="card-footer bg-white text-center">
                    <a href="{{ route('modules.sales-order.index') }}" class="text-decoration-none">
                        View All Orders <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Stats Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle text-info me-2"></i>Quick Info</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-users text-primary me-2"></i>
                                <span class="text-muted">Total Customers</span>
                            </div>
                            <strong class="text-primary">{{ number_format($totalCustomers) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-box text-success me-2"></i>
                                <span class="text-muted">Total Products</span>
                            </div>
                            <strong class="text-success">{{ number_format($totalProducts) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-clock text-warning me-2"></i>
                                <span class="text-muted">Current Time</span>
                            </div>
                            <strong class="text-dark">{{ now()->format('h:i A') }}</strong>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-store text-info me-2"></i>
                                <span class="text-muted">Your Store</span>
                            </div>
                            <strong class="text-dark">{{ auth()->user()->currentStore()->name ?? 'N/A' }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Tips -->
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h5 class="mb-3"><i class="fas fa-lightbulb me-2"></i>Sales Tip</h5>
                    <p class="mb-0 small opacity-75">
                        Great job today! Remember to follow up with customers for feedback and upsell opportunities.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Movements Section -->
    @can('stock-movement.view')
    <div class="row g-3 mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt text-primary me-2"></i>Today's Stock Movements</h5>
                    <span class="badge bg-primary">{{ $stockMovementsToday->count() }} movements</span>
                </div>
                <div class="card-body p-0">
                    @if($stockMovementsToday->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Time</th>
                                        <th>Product</th>
                                        <th>Transaction Type</th>
                                        <th>Movement</th>
                                        <th class="text-end">Quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockMovementsToday as $movement)
                                    <tr>
                                        <td><small class="text-muted">{{ $movement->created_at->format('h:i A') }}</small></td>
                                        <td><strong>{{ $movement->product->name ?? 'N/A' }}</strong></td>
                                        <td>
                                            @php
                                                $transactionColors = [
                                                    'sale' => 'success',
                                                    'purchase' => 'primary',
                                                    'sale_return' => 'info',
                                                    'purchase_return' => 'warning',
                                                    'damage' => 'danger',
                                                    'lost_missing' => 'dark',
                                                    'theft' => 'danger',
                                                    'expired' => 'secondary',
                                                    'transfer_in' => 'info',
                                                    'transfer_out' => 'warning',
                                                    'stock_correction' => 'warning',
                                                    'opening_stock' => 'primary',
                                                ];
                                                $color = $transactionColors[$movement->transaction_type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }} text-white">
                                                {{ ucfirst(str_replace('_', ' ', $movement->transaction_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($movement->movement_type === 'in')
                                                <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle">
                                                    <i class="fas fa-arrow-down me-1"></i>Stock IN
                                                </span>
                                            @elseif($movement->movement_type === 'out')
                                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger-subtle">
                                                    <i class="fas fa-arrow-up me-1"></i>Stock OUT
                                                </span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning-subtle">
                                                    <i class="fas fa-exchange-alt me-1"></i>Adjustment
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($movement->movement_type === 'in')
                                                <span class="fw-bold text-success">+{{ $movement->quantity }}</span>
                                            @elseif($movement->movement_type === 'out')
                                                <span class="fw-bold text-danger">-{{ $movement->quantity }}</span>
                                            @else
                                                <span class="fw-bold text-warning">{{ $movement->quantity }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-5 text-center">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No stock movements today</p>
                        </div>
                    @endif
                </div>
                @if($stockMovementsToday->count() > 0)
                <div class="card-footer bg-white text-center">
                    <a href="{{ route('modules.stock-movement.index') }}" class="text-decoration-none">
                        View All Stock Movements <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
