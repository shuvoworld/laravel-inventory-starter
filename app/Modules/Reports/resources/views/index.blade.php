@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Business Reports</h1>
</div>

<div class="row">
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Profit & Loss Report</h5>
                <p class="card-text text-muted">View revenue, costs, and profit analysis for any date range.</p>
                <a href="{{ route('modules.reports.profit-loss') }}" class="btn btn-primary">
                    <i class="fas fa-chart-line me-1"></i> View P&L Report
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-3x text-info mb-3"></i>
                <h5 class="card-title">Sales Report</h5>
                <p class="card-text text-muted">Analyze sales performance, trends, and customer insights.</p>
                <button class="btn btn-info" disabled>
                    <i class="fas fa-shopping-cart me-1"></i> Coming Soon
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-boxes fa-3x text-success mb-3"></i>
                <h5 class="card-title">Inventory Report</h5>
                <p class="card-text text-muted">Track stock levels, movements, and reorder recommendations.</p>
                <button class="btn btn-success" disabled>
                    <i class="fas fa-boxes me-1"></i> Coming Soon
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Customer Report</h5>
                <p class="card-text text-muted">Customer analytics, purchase history, and loyalty metrics.</p>
                <button class="btn btn-warning" disabled>
                    <i class="fas fa-users me-1"></i> Coming Soon
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-secondary">
            <div class="card-body text-center">
                <i class="fas fa-truck fa-3x text-secondary mb-3"></i>
                <h5 class="card-title">Purchase Report</h5>
                <p class="card-text text-muted">Supplier performance, purchase trends, and cost analysis.</p>
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-truck me-1"></i> Coming Soon
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-dark">
            <div class="card-body text-center">
                <i class="fas fa-file-export fa-3x text-dark mb-3"></i>
                <h5 class="card-title">Export Data</h5>
                <p class="card-text text-muted">Export reports to CSV, Excel, or PDF formats.</p>
                <button class="btn btn-dark" disabled>
                    <i class="fas fa-file-export me-1"></i> Coming Soon
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tachometer-alt me-2"></i>Quick Business Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-primary">{{ \App\Modules\SalesOrder\Models\SalesOrder::where('status', '!=', 'cancelled')->sum('total_amount') }}</h4>
                            <small class="text-muted">Total Sales Revenue</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-success">{{ \App\Modules\Products\Models\Product::sum('quantity_on_hand') }}</h4>
                            <small class="text-muted">Total Stock Items</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border-end">
                            <h4 class="text-warning">{{ \App\Modules\SalesOrder\Models\SalesOrder::count() }}</h4>
                            <small class="text-muted">Total Orders</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info">{{ \App\Modules\Customers\Models\Customer::count() }}</h4>
                        <small class="text-muted">Active Customers</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection