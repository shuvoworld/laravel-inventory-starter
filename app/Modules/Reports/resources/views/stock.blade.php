@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Stock Report</h1>
        <span class="badge bg-success">
            <i class="fas fa-exchange-alt me-1"></i> Data from Stock Movements
        </span>
    </div>
    <a href="{{ route('modules.reports.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Reports
    </a>
</div>

<!-- Stock Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $overview['summary']['total_products'] }}</h4>
                        <p class="card-text">Total Products</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-box fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ number_format($overview['summary']['total_stock_quantity']) }}</h4>
                        <p class="card-text">Total Stock Units</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-cubes fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">${{ number_format($overview['summary']['total_stock_value'], 2) }}</h4>
                        <p class="card-text">Total Stock Value</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title">{{ $overview['summary']['low_stock_count'] + $overview['summary']['out_of_stock_count'] }}</h4>
                        <p class="card-text">Items Needing Attention</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Status Distribution -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Stock Status Distribution</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="text-success fs-2">{{ $overview['summary']['healthy_stock_count'] }}</div>
                            <small class="text-muted">In Stock</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="text-warning fs-2">{{ $overview['summary']['low_stock_count'] }}</div>
                            <small class="text-muted">Low Stock</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <div class="text-danger fs-2">{{ $overview['summary']['out_of_stock_count'] }}</div>
                            <small class="text-muted">Out of Stock</small>
                        </div>
                    </div>
                </div>
                <div class="progress" style="height: 25px;">
                    <?php
                    $total = $overview['summary']['total_products'];
                    $healthy_pct = $total > 0 ? ($overview['summary']['healthy_stock_count'] / $total) * 100 : 0;
                    $low_pct = $total > 0 ? ($overview['summary']['low_stock_count'] / $total) * 100 : 0;
                    $out_pct = $total > 0 ? ($overview['summary']['out_of_stock_count'] / $total) * 100 : 0;
                    ?>
                    <div class="progress-bar bg-success" style="width: {{ $healthy_pct }}%" title="In Stock: {{ number_format($healthy_pct, 1) }}%">
                        {{ number_format($healthy_pct, 0) }}%
                    </div>
                    <div class="progress-bar bg-warning" style="width: {{ $low_pct }}%" title="Low Stock: {{ number_format($low_pct, 1) }}%">
                        {{ number_format($low_pct, 0) }}%
                    </div>
                    <div class="progress-bar bg-danger" style="width: {{ $out_pct }}%" title="Out of Stock: {{ number_format($out_pct, 1) }}%">
                        {{ number_format($out_pct, 0) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Stock Valuation</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <div class="fs-2">${{ number_format($valuation['total_cost_value'], 2) }}</div>
                            <small class="text-muted">Total Stock Cost Value</small>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Stock valuation based on Average Cost from purchase orders</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Quick Actions -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <a href="{{ route('modules.reports.stock.detailed') }}" class="btn btn-primary w-100">
                    <i class="fas fa-list-alt me-1"></i> Detailed Stock Report
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('modules.reports.stock.movement-trends') }}" class="btn btn-info w-100">
                    <i class="fas fa-chart-line me-1"></i> Movement Trends
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('modules.reports.stock.valuation') }}" class="btn btn-success w-100">
                    <i class="fas fa-calculator me-1"></i> Stock Valuation
                </a>
            </div>
          </div>
    </div>
</div>
@endsection