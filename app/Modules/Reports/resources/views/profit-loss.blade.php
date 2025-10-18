@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Profit & Loss Report</h1>
    <a href="{{ route('modules.reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Reports
    </a>
</div>

<!-- Period Selection and Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <!-- Period Type Tabs -->
        <div class="mb-3">
            <ul class="nav nav-pills" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'day' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'day']) }}">
                        <i class="fas fa-calendar-day me-1"></i> Daily
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'week' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'week']) }}">
                        <i class="fas fa-calendar-week me-1"></i> Weekly
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'month' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'month']) }}">
                        <i class="fas fa-calendar me-1"></i> Monthly
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'custom' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'custom']) }}">
                        <i class="fas fa-calendar-alt me-1"></i> Custom Range
                    </a>
                </li>
            </ul>
        </div>

        <!-- Date Range Form -->
        <form method="GET" action="{{ route('modules.reports.profit-loss') }}" class="row g-3">
            <input type="hidden" name="period_type" value="{{ $data['period_type'] }}">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $data['period']['start_date'] }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $data['period']['end_date'] }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Update Report
                </button>
                <a href="{{ route('modules.reports.profit-loss', ['period_type' => $data['period_type']]) }}" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-refresh me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Header -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar me-2"></i>Report Period: {{ $data['period']['formatted_period'] }}
        </h5>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                <h4 class="text-success">${{ number_format($data['revenue']['total_revenue'], 2) }}</h4>
                <p class="card-text">Total Revenue</p>
                <small class="text-muted">{{ $data['orders']['sales_orders_count'] }} orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-2x text-danger mb-2"></i>
                <h4 class="text-danger">${{ number_format($data['costs']['cogs'], 2) }}</h4>
                <p class="card-text">Cost of Goods Sold</p>
                <small class="text-muted">{{ number_format($data['revenue']['total_sales_quantity']) }} items sold</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                <h4 class="text-primary">${{ number_format($data['profit']['gross_profit'], 2) }}</h4>
                <p class="card-text">Gross Profit</p>
                <small class="text-muted">{{ number_format($data['profit']['gross_profit_margin'], 1) }}% margin</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice fa-2x text-warning mb-2"></i>
                <h4 class="text-warning">${{ number_format($data['costs']['operating_expenses'], 2) }}</h4>
                <p class="card-text">Operating Expenses</p>
                <small class="text-muted">Total expenses</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                <h4 class="text-primary">${{ number_format($data['profit']['gross_profit'], 2) }}</h4>
                <p class="card-text">Gross Profit</p>
                <small class="text-muted">{{ number_format($data['profit']['gross_profit_margin'], 1) }}% margin</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-coins fa-2x text-info mb-2"></i>
                <h4 class="text-info {{ $data['profit']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($data['profit']['net_profit'], 2) }}</h4>
                <p class="card-text">Net Profit</p>
                <small class="text-muted">{{ number_format($data['profit']['net_profit_margin'], 1) }}% margin</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Detailed P&L Statement -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-invoice-dollar me-2"></i>Profit & Loss Statement
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <!-- Revenue Section -->
                        <tr class="bg-light">
                            <td colspan="2"><strong>REVENUE</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-3">Sales Revenue</td>
                            <td class="text-end">${{ number_format($data['revenue']['total_revenue'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Sales Orders ({{ $data['orders']['sales_orders_count'] }})</td>
                            <td class="text-end text-muted">{{ number_format($data['revenue']['total_sales_quantity']) }} items</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Average Order Value</td>
                            <td class="text-end text-muted">${{ number_format($data['revenue']['average_order_value'], 2) }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td><strong>Total Revenue</strong></td>
                            <td class="text-end"><strong>${{ number_format($data['revenue']['total_revenue'], 2) }}</strong></td>
                        </tr>

                        <!-- Cost Section -->
                        <tr class="bg-light">
                            <td colspan="2"><strong>COSTS</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-3">Cost of Goods Sold</td>
                            <td class="text-end text-danger">${{ number_format($data['costs']['cogs'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Purchase Orders ({{ $data['orders']['purchase_orders_count'] }})</td>
                            <td class="text-end text-muted">${{ number_format($data['costs']['total_purchases'], 2) }}</td>
                        </tr>
                        <tr class="border-bottom">
                            <td><strong>Gross Profit</strong></td>
                            <td class="text-end"><strong class="text-success">${{ number_format($data['profit']['gross_profit'], 2) }}</strong></td>
                        </tr>

                        <!-- Operating Expenses Section -->
                        <tr class="bg-light">
                            <td colspan="2"><strong>OPERATING EXPENSES</strong></td>
                        </tr>
                        @if(count($data['expenses_by_category']) > 0)
                            @foreach($data['expenses_by_category'] as $expense)
                                <tr>
                                    <td class="ps-3">{{ $expense['category_label'] }}</td>
                                    <td class="text-end text-warning">${{ number_format($expense['total'], 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="ps-3 text-muted">No operating expenses recorded</td>
                                <td class="text-end text-muted">$0.00</td>
                            </tr>
                        @endif
                        <tr class="border-bottom">
                            <td><strong>Total Operating Expenses</strong></td>
                            <td class="text-end"><strong class="text-warning">${{ number_format($data['costs']['operating_expenses'], 2) }}</strong></td>
                        </tr>

                        <!-- Net Profit Section -->
                        <tr class="bg-light">
                            <td colspan="2"><strong>NET PROFIT</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-3">Gross Profit</td>
                            <td class="text-end text-success">${{ number_format($data['profit']['gross_profit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3">Less: Operating Expenses</td>
                            <td class="text-end text-warning">${{ number_format($data['costs']['operating_expenses'], 2) }}</td>
                        </tr>
                        <tr class="border-top border-bottom">
                            <td><strong>Net Profit (Loss)</strong></td>
                            <td class="text-end">
                                <strong class="{{ $data['profit']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($data['profit']['net_profit'], 2) }}
                                </strong>
                            </td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Net Profit Margin</td>
                            <td class="text-end text-muted {{ $data['profit']['net_profit_margin'] >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format($data['profit']['net_profit_margin'], 1) }}%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Top Selling Products -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy me-2"></i>Top Selling Products
                </h5>
            </div>
            <div class="card-body">
                @if($data['top_products']->count() > 0)
                    @foreach($data['top_products'] as $index => $product)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <span class="badge badge-primary me-2">{{ $index + 1 }}</span>
                                <strong>{{ $product['product_name'] }}</strong>
                                <br>
                                <small class="text-muted">Qty: {{ number_format($product['quantity_sold']) }}</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">${{ number_format($product['total_revenue'], 2) }}</strong>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">No sales data available for this period.</p>
                @endif
            </div>
        </div>

        <!-- Performance Indicators -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-gauge me-2"></i>Performance Indicators
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Profit Margin</span>
                        <strong>{{ number_format($data['profit']['gross_profit_margin'], 1) }}%</strong>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-success" style="width: {{ min($data['profit']['gross_profit_margin'], 100) }}%"></div>
                    </div>
                </div>

                @php $revenueGrowth = 15; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Revenue Growth</span>
                        <strong class="text-success">+{{ $revenueGrowth }}%</strong>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-primary" style="width: {{ min($revenueGrowth, 100) }}%"></div>
                    </div>
                </div>

                <div class="text-center">
                    <small class="text-muted">Compared to previous period</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trend Analysis and Period Breakdown -->
@if($data['period_type'] !== 'custom' && count($data['period_breakdown']) > 1)
<div class="row mt-4">
    <div class="col-12">
        <!-- Trend Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>{{ ucfirst($data['period_type']) }}ly Profit & Loss Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="profitLossChart" style="height: 400px;"></canvas>
            </div>
        </div>

        <!-- Period Breakdown Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>{{ ucfirst($data['period_type']) }}ly Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Period</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">COGS</th>
                                <th class="text-end">Op. Expenses</th>
                                <th class="text-end">Net Profit</th>
                                <th class="text-end">Profit %</th>
                                <th class="text-center">Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['period_breakdown'] as $period)
                                @php
                                    $netProfitMargin = $period['revenue'] > 0 ? ($period['net_profit'] / $period['revenue']) * 100 : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $period['period'] }}</strong></td>
                                    <td class="text-end text-success">${{ number_format($period['revenue'], 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($period['cogs'], 2) }}</td>
                                    <td class="text-end text-warning">${{ number_format($period['operating_expenses'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="{{ $period['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            ${{ number_format($period['net_profit'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="{{ $netProfitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($netProfitMargin, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $period['orders_count'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th class="text-end">${{ number_format($data['revenue']['total_revenue'], 2) }}</th>
                                <th class="text-end">${{ number_format($data['costs']['cogs'], 2) }}</th>
                                <th class="text-end">${{ number_format($data['costs']['operating_expenses'], 2) }}</th>
                                <th class="text-end">${{ number_format($data['profit']['net_profit'], 2) }}</th>
                                <th class="text-end">{{ number_format($data['profit']['net_profit_margin'], 1) }}%</th>
                                <th class="text-center">{{ $data['orders']['sales_orders_count'] }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($data['period_type'] !== 'custom' && count($data['period_breakdown']) > 1)
    // Profit & Loss Trend Chart
    const ctx = document.getElementById('profitLossChart').getContext('2d');
    const chartData = @json($data['period_breakdown']);

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.period),
            datasets: [
                {
                    label: 'Revenue',
                    data: chartData.map(item => item.revenue),
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'COGS',
                    data: chartData.map(item => item.cogs),
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Operating Expenses',
                    data: chartData.map(item => item.operating_expenses),
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Net Profit',
                    data: chartData.map(item => item.net_profit),
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
                title: {
                    display: true,
                    text: '{{ ucfirst($data['period_type']) }}ly Profit & Loss Trend'
                },
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
    @endif
});
</script>
@endpush
@endsection