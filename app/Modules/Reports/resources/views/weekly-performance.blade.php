@extends('layouts.app')

@section('title', 'Weekly Product Performance Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Best-Sellers & Slow-Movers Report</h1>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('reports.weekly-performance') }}" class="d-flex gap-2">
                        <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="form-control" placeholder="Start Date">
                        <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="form-control" placeholder="End Date">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                    <a href="{{ route('reports.weekly-performance') }}" class="btn btn-outline-secondary">This Week</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-1">Report Period: {{ $report['period']['formatted_range'] }} (Week {{ $report['period']['week_number'] }}, {{ $report['period']['year'] }})</h5>
                            <p class="text-muted mb-0">Analysis of product performance for the selected week</p>
                        </div>
                        <div class="text-end">
                            <h6 class="mb-1">Total Products Sold: {{ number_format($report['summary']['total_products_sold']) }}</h6>
                            <h6 class="mb-0">Total Revenue: ${{ number_format($report['summary']['total_revenue'], 2) }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Week Comparison -->
    @if($comparison['revenue_change'] != 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card {{ $comparison['revenue_change'] > 0 ? 'border-success' : 'border-danger' }}">
                <div class="card-body">
                    <h6 class="card-title">Week-over-Week Comparison</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted">Revenue Change</small>
                            <h5 class="{{ $comparison['revenue_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $comparison['revenue_change'] > 0 ? '+' : '' }}${{ number_format($comparison['revenue_change'], 2) }}
                                ({{ $comparison['revenue_change'] > 0 ? '+' : '' }}{{ $comparison['revenue_change_percent'] }}%)
                            </h5>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Quantity Change</small>
                            <h5 class="{{ $comparison['quantity_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $comparison['quantity_change'] > 0 ? '+' : '' }}{{ number_format($comparison['quantity_change']) }} units
                            </h5>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Products Change</small>
                            <h5 class="{{ $comparison['products_change'] > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $comparison['products_change'] > 0 ? '+' : '' }}{{ number_format($comparison['products_change']) }} items
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <!-- Best Sellers -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-trophy"></i> Top 5 Best-Sellers (by Revenue)
                    </h5>
                </div>
                <div class="card-body">
                    @if($report['best_sellers']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Profit</th>
                                        <th class="text-center">Orders</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['best_sellers'] as $index => $product)
                                        <tr>
                                            <td>
                                                <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : ($index == 2 ? 'danger' : 'light')) }}">
                                                    {{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $product['product_name'] }}</strong>
                                                <br>
                                                <small class="text-muted">SKU: {{ $product['product_sku'] }}</small>
                                            </td>
                                            <td class="text-center">{{ $product['total_quantity'] }}</td>
                                            <td class="text-end">${{ number_format($product['total_revenue'], 2) }}</td>
                                            <td class="text-end">
                                                ${{ number_format($product['total_profit'], 2) }}
                                                <br>
                                                <small class="text-muted">{{ $product['profit_margin'] }}%</small>
                                            </td>
                                            <td class="text-center">{{ $product['orders_count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2">Totals</th>
                                        <th class="text-center">{{ $report['best_sellers']->sum('total_quantity') }}</th>
                                        <th class="text-end">${{ number_format($report['best_sellers']->sum('total_revenue'), 2) }}</th>
                                        <th class="text-end">${{ number_format($report['best_sellers']->sum('total_profit'), 2) }}</th>
                                        <th class="text-center">{{ $report['best_sellers']->sum('orders_count') }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No sales data available for this period.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Slow Movers -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Top 5 Slow-Movers
                    </h5>
                </div>
                <div class="card-body">
                    @if($report['slow_movers']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Markup</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['slow_movers'] as $product)
                                        <tr class="{{ $product['total_quantity'] == 0 ? 'table-danger' : '' }}">
                                            <td>
                                                <strong>{{ $product['product_name'] }}</strong>
                                                <br>
                                                <small class="text-muted">SKU: {{ $product['product_sku'] }}</small>
                                                @if($product['total_quantity'] == 0)
                                                    <br>
                                                    <span class="badge bg-danger">No Sales</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <strong>{{ $product['total_quantity'] }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $product['orders_count'] }} order(s)</small>
                                            </td>
                                            <td class="text-end">${{ number_format($product['total_revenue'], 2) }}</td>
                                            <td class="text-end">{{ $product['markup_percentage'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">All products performed well!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    @if($report['recommendations'])
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-lightbulb"></i> Recommendations for Slow-Movers
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($report['recommendations'] as $recommendation)
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-info">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            {{ $recommendation['product_name'] }}
                                            <small class="text-muted">({{ $recommendation['product_sku'] }})</small>
                                        </h6>
                                        <p class="text-muted mb-2">
                                            <strong>Issue:</strong> {{ $recommendation['issue'] }}
                                        </p>
                                        <ul class="mb-0">
                                            @foreach($recommendation['recommendations'] as $rec)
                                                <li>{{ $rec }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <h6>Overall Strategy for Slow-Movers:</h6>
                        <div class="alert alert-info">
                            {{ app(\App\Services\WeeklyProductPerformanceService::class)->getSlowMoversSummary($report['slow_movers']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <a href="{{ route('modules.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
                <a href="{{ route('reports.daily-sales') }}" class="btn btn-outline-info">
                    <i class="fas fa-calendar-day"></i> Daily Sales Report
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.border-left-info {
    border-left: 4px solid #17a2b8 !important;
}

@media print {
    .btn, .d-flex.justify-content-between {
        display: none !important;
    }

    .card {
        page-break-inside: avoid;
        margin-bottom: 1rem;
    }

    .container-fluid {
        max-width: 100%;
        padding: 0;
    }

    .table-responsive {
        overflow: visible !important;
    }

    .alert {
        page-break-inside: avoid;
    }
}
</style>
@endsection