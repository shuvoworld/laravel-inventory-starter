@extends('layouts.app')

@section('title', 'Daily Sales Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Daily Sales Report</h1>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('reports.daily-sales') }}" class="d-flex gap-2">
                        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                    <a href="{{ route('reports.daily-sales') }}" class="btn btn-outline-secondary">Today</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <h2 class="card-text">${{ $report['formatted_metrics']['total_revenue'] }}</h2>
                    <small class="text-white-50">{{ $report['report_date'] }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Transactions</h5>
                    <h2 class="card-text">{{ $report['formatted_metrics']['total_transactions'] }}</h2>
                    <small class="text-white-50">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Avg Transaction</h5>
                    <h2 class="card-text">${{ $report['formatted_metrics']['average_transaction_value'] }}</h2>
                    <small class="text-white-50">Per Order</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Performance</h5>
                    <p class="card-text">{{ ucfirst($report['performance_summary']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Products -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 3 Best-Selling Products</h5>
                </div>
                <div class="card-body">
                    @if($report['top_products']->count() > 0)
                        @foreach($report['top_products'] as $index => $product)
                            <div class="d-flex justify-content-between align-items-center mb-3 {{ $index < 2 ? 'border-bottom' : '' }}">
                                <div>
                                    <h6 class="mb-1">{{ $product->product_name }}</h6>
                                    <small class="text-muted">{{ $product->orders_count }} order(s)</small>
                                </div>
                                <div class="text-end">
                                    <strong>{{ $product->total_quantity }} units</strong>
                                    <br>
                                    <small class="text-success">${{ number_format($product->total_revenue, 2) }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No sales recorded for this date.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daily Performance Summary</h5>
                </div>
                <div class="card-body">
                    <p class="lead">{{ $report['performance_summary'] }}</p>

                    <hr>

                    <h6>Key Metrics:</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-dollar-sign text-success"></i>
                            <strong>Total Revenue:</strong> ${{ $report['formatted_metrics']['total_revenue'] }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-shopping-cart text-primary"></i>
                            <strong>Transactions:</strong> {{ $report['formatted_metrics']['total_transactions'] }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chart-line text-info"></i>
                            <strong>Average Value:</strong> ${{ $report['formatted_metrics']['average_transaction_value'] }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Trends -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">7-Day Sales Trend</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Revenue</th>
                                    <th>Transactions</th>
                                    <th>Avg Value</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($weeklyTrends as $trend)
                                    <tr>
                                        <td>{{ $trend['date'] }}</td>
                                        <td>${{ number_format($trend['revenue'], 2) }}</td>
                                        <td>{{ $trend['transactions'] }}</td>
                                        <td>${{ number_format($trend['avg_value'], 2) }}</td>
                                        <td>
                                            @if($trend['transactions'] == 0)
                                                <span class="badge bg-secondary">No Sales</span>
                                            @elseif($trend['revenue'] >= 10000)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif($trend['revenue'] >= 5000)
                                                <span class="badge bg-primary">Good</span>
                                            @elseif($trend['revenue'] >= 1000)
                                                <span class="badge bg-info">Moderate</span>
                                            @else
                                                <span class="badge bg-warning">Low</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, .d-flex.justify-content-between {
        display: none !important;
    }

    .card {
        page-break-inside: avoid;
    }

    .container-fluid {
        max-width: 100%;
        padding: 0;
    }
}
</style>
@endsection