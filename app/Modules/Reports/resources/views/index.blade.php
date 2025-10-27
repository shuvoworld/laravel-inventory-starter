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
                <i class="fas fa-calendar-day fa-3x text-info mb-3"></i>
                <h5 class="card-title">Daily Sales Report</h5>
                <p class="card-text text-muted">View daily sales performance, top products, and trends.</p>
                <a href="{{ route('modules.reports.daily-sales') }}" class="btn btn-info">
                    <i class="fas fa-calendar-day me-1"></i> Daily Sales
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-3x text-info mb-3"></i>
                <h5 class="card-title">Daily Purchase Report</h5>
                <p class="card-text text-muted">View daily purchase performance, top products, and trends.</p>
                <a href="{{ route('modules.reports.daily-purchase') }}" class="btn btn-info">
                    <i class="fas fa-shopping-cart me-1"></i> Daily Purchase
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-cubes fa-3x text-success mb-3"></i>
                <h5 class="card-title">Stock Report</h5>
                <p class="card-text text-muted">Track stock levels, movements, and reorder recommendations.</p>
                <a href="{{ route('modules.reports.stock') }}" class="btn btn-success">
                    <i class="fas fa-cubes me-1"></i> Stock Report
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-warning mb-3"></i>
                <h5 class="card-title">Customer Due Report</h5>
                <p class="card-text text-muted">Track outstanding payments and overdue customer orders.</p>
                <a href="{{ route('modules.reports.customer-due') }}" class="btn btn-warning">
                    <i class="fas fa-users me-1"></i> Customer Dues
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-secondary">
            <div class="card-body text-center">
                <i class="fas fa-user-friends fa-3x text-secondary mb-3"></i>
                <h5 class="card-title">Supplier Due Report</h5>
                <p class="card-text text-muted">Track outstanding payments and overdue supplier invoices.</p>
                <a href="{{ route('modules.reports.supplier-due') }}" class="btn btn-secondary">
                    <i class="fas fa-user-friends me-1"></i> Supplier Dues
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-dark">
            <div class="card-body text-center">
                <i class="fas fa-calendar-week fa-3x text-dark mb-3"></i>
                <h5 class="card-title">Weekly Performance</h5>
                <p class="card-text text-muted">Analyze weekly product performance and trends.</p>
                <a href="{{ route('modules.reports.weekly-performance') }}" class="btn btn-dark">
                    <i class="fas fa-calendar-week me-1"></i> Weekly Report
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                <h5 class="card-title">Low Stock Alert</h5>
                <p class="card-text text-muted">View products with low inventory levels.</p>
                <a href="{{ route('modules.reports.low-stock-alert') }}" class="btn btn-danger">
                    <i class="fas fa-exclamation-triangle me-1"></i> Low Stock
                </a>
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