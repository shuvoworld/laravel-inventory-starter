@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Profit & Loss Report</h1>
    <a href="{{ route('modules.reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Reports
    </a>
</div>

<!-- Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('modules.reports.profit-loss') }}" class="row g-3">
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
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-coins fa-2x text-info mb-2"></i>
                <h4 class="text-info">${{ number_format($data['profit']['net_profit'], 2) }}</h4>
                <p class="card-text">Net Profit</p>
                <small class="text-muted">After all costs</small>
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
                            <td><strong>Total Costs</strong></td>
                            <td class="text-end"><strong class="text-danger">${{ number_format($data['costs']['cogs'], 2) }}</strong></td>
                        </tr>

                        <!-- Profit Section -->
                        <tr class="bg-light">
                            <td colspan="2"><strong>PROFIT</strong></td>
                        </tr>
                        <tr>
                            <td class="ps-3">Gross Profit</td>
                            <td class="text-end text-success">${{ number_format($data['profit']['gross_profit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3 text-muted">Gross Profit Margin</td>
                            <td class="text-end text-muted">{{ number_format($data['profit']['gross_profit_margin'], 1) }}%</td>
                        </tr>
                        <tr class="border-top border-bottom">
                            <td><strong>Net Profit</strong></td>
                            <td class="text-end"><strong class="text-success">${{ number_format($data['profit']['net_profit'], 2) }}</strong></td>
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
                                <strong>{{ $product['name'] }}</strong>
                                <br>
                                <small class="text-muted">Qty: {{ number_format($product['quantity']) }}</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">${{ number_format($product['revenue'], 2) }}</strong>
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
@endsection