@extends('layouts.app')

@section('title', 'Low-Stock Inventory Alert')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Low-Stock Inventory Alert
                </h1>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('reports.low-stock-alert') }}" class="d-flex gap-2 align-items-center">
                        <label for="threshold" class="form-label mb-0 me-2">Alert Threshold:</label>
                        <input type="number" name="threshold" id="threshold" value="{{ $threshold }}" min="1" max="1000" class="form-control" style="width: 100px;">
                        <span class="me-2">units</span>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-sync"></i> Update
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body text-center">
                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                    <h5 class="card-title">Out of Stock</h5>
                    <h2>{{ $dashboardData['out_of_stock'] }}</h2>
                    <small>Immediate Action Required</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
                    <h5 class="card-title">Critical</h5>
                    <h2>{{ $dashboardData['critical'] }}</h2>
                    <small>≤ 2 units remaining</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <h5 class="card-title">Low Stock</h5>
                    <h2>{{ $dashboardData['low'] }}</h2>
                    <small>3-10 units remaining</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-2x mb-2"></i>
                    <h5 class="card-title">Total Alert Items</h5>
                    <h2>{{ $dashboardData['total'] }}</h2>
                    <small>Need attention</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alert Message -->
    @if($report['most_critical_item'])
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-{{ $report['most_critical_item']['urgency_level'] == 'Urgent - Out of Stock' ? 'danger' : 'warning' }} alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-bell"></i> {{ $report['most_critical_item']['urgency_level'] }}
                </h5>
                <p class="mb-0">{{ app(\App\Services\LowStockAlertService::class)->generateCriticalAlertSummary($report) }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @else
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-check-circle"></i> All Systems Green
                </h5>
                <p class="mb-0">Excellent! No products are currently below the {{ $threshold }} unit threshold. All inventory levels are healthy.</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Low Stock Products Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-table"></i> Low-Stock Products Report
                    </h5>
                    <small class="text-muted">
                        Generated on {{ $report['generated_at'] }} • Threshold: {{ $threshold }} units
                    </small>
                </div>
                <div class="card-body">
                    @if($report['low_stock_products']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th class="text-center">Current Stock</th>
                                        <th class="text-center">Reorder Level</th>
                                        <th class="text-center">Avg Daily Sales</th>
                                        <th class="text-center">Days of Stock</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end">Value</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['low_stock_products'] as $product)
                                        <tr class="{{ $product['current_stock'] == 0 ? 'table-danger' : ($product['current_stock'] <= 2 ? 'table-warning' : '') }}">
                                            <td>
                                                <strong>{{ $product['product_name'] }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $product['brand_name'] }}</small>
                                            </td>
                                            <td>
                                                <code>{{ $product['product_sku'] }}</code>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $product['current_stock'] == 0 ? 'danger' : ($product['current_stock'] <= 2 ? 'warning' : 'info') }} fs-6">
                                                    {{ $product['current_stock'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $product['reorder_level'] }}</td>
                                            <td class="text-center">
                                                {{ $product['average_daily_sales'] }}
                                                @if($product['average_daily_sales'] == 0)
                                                    <br>
                                                    <small class="text-muted">No sales</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($product['days_of_stock_remaining'] >= 999)
                                                    <span class="text-muted">∞</span>
                                                @else
                                                    <strong>{{ round($product['days_of_stock_remaining'], 1) }}</strong>
                                                    <br>
                                                    <small class="text-muted">days</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $product['urgency_level'] == 'Urgent - Out of Stock' ? 'danger' : ($product['days_of_stock_remaining'] <= 3 ? 'warning' : 'info') }}">
                                                    {{ $product['stock_status'] }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                ${{ number_format($product['current_stock'] * $product['cost_price'], 2) }}
                                                <br>
                                                <small class="text-muted">@ ${{ number_format($product['cost_price'], 2) }} each</small>
                                            </td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-primary" onclick="showReorderDetails({{ $product['product_id'] }})">
                                                    <i class="fas fa-shopping-cart"></i> Reorder
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3">Total: {{ $report['low_stock_products']->count() }} products</th>
                                        <th colspan="4" class="text-end">Total Inventory Value at Risk:</th>
                                        <th class="text-end">
                                            <strong>${{ number_format($report['summary']['total_inventory_value_at_risk'], 2) }}</strong>
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4 class="text-success">No Low Stock Items</h4>
                            <p class="text-muted">All products have sufficient stock levels above the {{ $threshold }} unit threshold.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Most Critical Item Details -->
    @if($report['most_critical_item'])
    <div class="row mt-4">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Most Critical Item - Immediate Action Required
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>{{ $report['most_critical_item']['product_name'] }}</h6>
                            <p class="mb-2">
                                <strong>SKU:</strong> <code>{{ $report['most_critical_item']['product_sku'] }}</code><br>
                                <strong>Current Stock:</strong>
                                <span class="badge bg-danger">{{ $report['most_critical_item']['current_stock'] }} units</span><br>
                                <strong>Days Remaining:</strong>
                                {{ $report['most_critical_item']['days_of_stock_remaining'] >= 999 ? 'No sales trend' : round($report['most_critical_item']['days_of_stock_remaining'], 1) . ' days' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Recommended Action</h6>
                            <p class="mb-2">
                                <strong>Reorder Quantity:</strong> {{ $report['most_critical_item']['recommended_reorder_quantity'] }} units<br>
                                <strong>Urgency:</strong> <span class="badge bg-danger">{{ $report['most_critical_item']['urgency_level'] }}</span><br>
                                @if($report['most_critical_item']['estimated_stockout_date'])
                                    <strong>Est. Stockout Date:</strong> {{ $report['most_critical_item']['estimated_stockout_date'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="alert alert-warning">
                        <strong>Priority Action:</strong> {{ $report['most_critical_item']['product_name'] }} is the most critical item that needs immediate reordering to avoid stockout.
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">Quick Statistics</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-chart-line text-info"></i>
                            <strong>Avg Days of Stock:</strong> {{ $report['summary']['average_days_of_stock'] }} days
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-dollar-sign text-success"></i>
                            <strong>Value at Risk:</strong> ${{ number_format($report['summary']['total_inventory_value_at_risk'], 2) }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-box-open text-warning"></i>
                            <strong>Items Needing Attention:</strong> {{ $report['summary']['total_low_stock_items'] }}
                        </li>
                        @if($report['summary']['out_of_stock_count'] > 0)
                        <li class="mb-2">
                            <i class="fas fa-times-circle text-danger"></i>
                            <strong>Out of Stock:</strong> {{ $report['summary']['out_of_stock_count'] }}
                        </li>
                        @endif
                    </ul>
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
                <button onclick="exportToCSV()" class="btn btn-outline-success">
                    <i class="fas fa-file-csv"></i> Export CSV
                </button>
                <a href="{{ route('modules.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Reorder Modal -->
<div class="modal fade" id="reorderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reorder Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reorderDetails">
                    <!-- Details will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="createPurchaseOrder()">Create Purchase Order</button>
            </div>
        </div>
    </div>
</div>

<script>
function showReorderDetails(productId) {
    // Find the product data
    const products = @json($report['low_stock_products']);
    const product = products.find(p => p.product_id == productId);

    if (product) {
        const details = `
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Product:</strong> ${product.product_name}</p>
                    <p><strong>SKU:</strong> ${product.product_sku}</p>
                    <p><strong>Current Stock:</strong> ${product.current_stock} units</p>
                    <p><strong>Reorder Level:</strong> ${product.reorder_level} units</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Recommended Quantity:</strong> ${product.recommended_reorder_quantity} units</p>
                    <p><strong>Unit Cost:</strong> $${product.cost_price.toFixed(2)}</p>
                    <p><strong>Estimated Total:</strong> $${(product.recommended_reorder_quantity * product.cost_price).toFixed(2)}</p>
                    <p><strong>Urgency:</strong> ${product.urgency_level}</p>
                </div>
            </div>
            <div class="mt-3">
                <label for="reorderQuantity" class="form-label">Reorder Quantity:</label>
                <input type="number" class="form-control" id="reorderQuantity" value="${product.recommended_reorder_quantity}" min="1">
            </div>
        `;

        document.getElementById('reorderDetails').innerHTML = details;
        new bootstrap.Modal(document.getElementById('reorderModal')).show();
    }
}

function createPurchaseOrder() {
    // Placeholder for purchase order creation
    alert('Purchase order creation would be implemented here. This would integrate with your purchase order system.');
    bootstrap.Modal.getInstance(document.getElementById('reorderModal')).hide();
}

function exportToCSV() {
    const products = @json($report['low_stock_products']);
    let csv = 'Product Name,SKU,Current Stock,Reorder Level,Avg Daily Sales,Days of Stock,Status,Value\n';

    products.forEach(product => {
        csv += `"${product.product_name}","${product.product_sku}",${product.current_stock},${product.reorder_level},${product.average_daily_sales},${product.days_of_stock_remaining},"${product.stock_status}",${product.current_stock * product.cost_price}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `low-stock-alert-${{ now()->format('Y-m-d') }}.csv`;
    a.click();
}
</script>

<style>
@media print {
    .btn, .d-flex.justify-content-between, .modal {
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

    .alert-dismissible .btn-close {
        display: none !important;
    }
}
</style>
@endsection