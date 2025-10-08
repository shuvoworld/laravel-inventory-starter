@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Dashboard</h1>
            <p class="text-muted mb-0">{{ $companyInfo['name'] ?? 'Store Admin Dashboard' }}</p>
        </div>
        <div class="text-end">
            <small class="text-muted">{{ now()->format('F j, Y') }}</small>
        </div>
    </div>

    <!-- Financial Overview Cards -->
    <div class="row g-3 mb-4">
        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Revenue (This Month)</p>
                            <h3 class="mb-0">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($currentMonthData['revenue'], 2) }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                            <i class="fas fa-dollar-sign text-primary"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($revenueGrowth >= 0)
                            <i class="fas fa-arrow-up text-success me-1 small"></i>
                            <span class="text-success small">{{ number_format($revenueGrowth, 1) }}%</span>
                        @else
                            <i class="fas fa-arrow-down text-danger me-1 small"></i>
                            <span class="text-danger small">{{ number_format(abs($revenueGrowth), 1) }}%</span>
                        @endif
                        <span class="text-muted ms-2 small">vs last month</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Net Profit</p>
                            <h3 class="mb-0">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($currentMonthData['net_profit'], 2) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded">
                            <i class="fas fa-chart-line text-success"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($profitGrowth >= 0)
                            <i class="fas fa-arrow-up text-success me-1 small"></i>
                            <span class="text-success small">{{ number_format($profitGrowth, 1) }}%</span>
                        @else
                            <i class="fas fa-arrow-down text-danger me-1 small"></i>
                            <span class="text-danger small">{{ number_format(abs($profitGrowth), 1) }}%</span>
                        @endif
                        <span class="text-muted ms-2 small">vs last month</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Operating Expenses</p>
                            <h3 class="mb-0">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($currentMonthData['operating_expenses'], 2) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded">
                            <i class="fas fa-receipt text-warning"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($expenseGrowth >= 0)
                            <i class="fas fa-arrow-up text-danger me-1 small"></i>
                            <span class="text-danger small">{{ number_format($expenseGrowth, 1) }}%</span>
                        @else
                            <i class="fas fa-arrow-down text-success me-1 small"></i>
                            <span class="text-success small">{{ number_format(abs($expenseGrowth), 1) }}%</span>
                        @endif
                        <span class="text-muted ms-2 small">vs last month</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Total Orders</p>
                            <h3 class="mb-0">{{ number_format($currentMonthData['orders_count']) }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-2 rounded">
                            <i class="fas fa-shopping-cart text-info"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted small">Avg: {{ $currencySettings['symbol'] ?? '$' }}{{ number_format($currentMonthData['average_order_value'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Margins & Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Last 30 Days Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="financialChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Customers</span>
                            <strong>{{ number_format($totalCustomers) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Products</span>
                            <strong>{{ number_format($totalProducts) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Low Stock Items</span>
                            <strong class="text-{{ $lowStockProducts > 0 ? 'danger' : 'success' }}">{{ number_format($lowStockProducts) }}</strong>
                        </div>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Pending Expenses</span>
                            <strong class="text-warning">{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($pendingExpenses, 2) }}</strong>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Gross Margin</span>
                            <strong class="text-success">{{ number_format($currentMonthData['gross_profit_margin'], 1) }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Sales Orders</h5>
                    <a href="{{ route('modules.sales-order.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order #</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSalesOrders as $order)
                                <tr>
                                    <td><a href="{{ route('modules.sales-order.show', $order->id) }}" class="text-decoration-none">#{{ $order->order_number }}</a></td>
                                    <td>{{ $order->customer->name ?? 'N/A' }}</td>
                                    <td>{{ $order->order_date->format('M d, Y') }}</td>
                                    <td>{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($order->total_amount, 2) }}</td>
                                    <td><span class="badge bg-{{ $order->status === 'delivered' ? 'success' : 'warning' }}">{{ ucfirst($order->status) }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No recent orders</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Expenses</h5>
                    <a href="{{ route('modules.operating-expenses.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentExpenses as $expense)
                                <tr>
                                    <td>{{ Str::limit($expense->description, 30) }}</td>
                                    <td>{{ $expense->expense_date->format('M d') }}</td>
                                    <td>{{ $currencySettings['symbol'] ?? '$' }}{{ number_format($expense->amount, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">No recent expenses</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Financial Trend Chart
    const ctx = document.getElementById('financialChart').getContext('2d');
    const trendData = @json($trendData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [
                {
                    label: 'Revenue',
                    data: trendData.map(d => d.revenue),
                    borderColor: 'rgb(102, 126, 234)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Net Profit',
                    data: trendData.map(d => d.net_profit),
                    borderColor: 'rgb(40, 199, 111)',
                    backgroundColor: 'rgba(40, 199, 111, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Expenses',
                    data: trendData.map(d => d.expenses),
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += '{{ $currencySettings['symbol'] ?? '$' }}' + context.parsed.y.toFixed(2);
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '{{ $currencySettings['symbol'] ?? '$' }}' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
