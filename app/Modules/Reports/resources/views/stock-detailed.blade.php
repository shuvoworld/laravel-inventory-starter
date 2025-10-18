@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Detailed Stock Report</h1>
    <a href="{{ route('modules.reports.stock') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Stock Report
    </a>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('modules.reports.stock.detailed') }}">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search Products</label>
                    <input type="text" id="search" name="search" class="form-control"
                           placeholder="Name, SKU, or Description..." value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="stock_status" class="form-label">Stock Status</label>
                    <select id="stock_status" name="stock_status" class="form-select">
                        <option value="">All Status</option>
                        <option value="in_stock" {{ ($filters['stock_status'] ?? '') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ ($filters['stock_status'] ?? '') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                        <option value="out_of_stock" {{ ($filters['stock_status'] ?? '') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="brand_id" class="form-label">Brand</label>
                    <select id="brand_id" name="brand_id" class="form-select">
                        <option value="">All Brands</option>
                        @foreach(App\Modules\Brand\Models\Brand::all() as $brand)
                        <option value="{{ $brand->id }}" {{ ($filters['brand_id'] ?? '') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            @if(!empty(array_filter($filters)))
            <div class="mt-3">
                <a href="{{ route('modules.reports.stock.detailed') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear Filters
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $detailedStock->count() }}</h4>
                <p class="card-text mb-0">Products Found</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>{{ number_format($detailedStock->sum('current_stock')) }}</h4>
                <p class="card-text mb-0">Total Units</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>${{ number_format($detailedStock->sum('total_value'), 2) }}</h4>
                <p class="card-text mb-0">Total Value</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>${{ number_format($detailedStock->sum('potential_profit'), 2) }}</h4>
                <p class="card-text mb-0">Potential Profit</p>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Stock Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Stock Details</h5>
        <div>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="stockTable">
                <thead class="table-dark">
                    <tr>
                        <th>Product</th>
                        <th>Brand</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-end">Cost Price</th>
                        <th class="text-end">Selling Price</th>
                        <th class="text-end">Total Value</th>
                        <th class="text-end">Profit Margin</th>
                        <th class="text-center">30-Day Sales</th>
                        <th class="text-center">Days of Inventory</th>
                        <th class="text-center">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detailedStock as $product)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $product['name'] }}</div>
                            <small class="text-muted">SKU: {{ $product['sku'] }}</small>
                        </td>
                        <td>{{ $product['brand'] }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $product['current_stock'] <= 0 ? 'danger' : ($product['current_stock'] <= $product['reorder_level'] ? 'warning' : 'success') }}">
                                {{ $product['current_stock'] }}
                            </span>
                        </td>
                        <td class="text-center">{{ $product['reorder_level'] }}</td>
                        <td class="text-end">${{ number_format($product['cost_price'], 2) }}</td>
                        <td class="text-end">${{ number_format($product['selling_price'], 2) }}</td>
                        <td class="text-end">${{ number_format($product['total_value'], 2) }}</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $product['profit_margin'] >= 30 ? 'success' : ($product['profit_margin'] >= 10 ? 'warning' : 'danger') }}">
                                {{ number_format($product['profit_margin'], 1) }}%
                            </span>
                        </td>
                        <td class="text-center">{{ $product['recent_sales'] }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $product['days_of_inventory'] >= 90 ? 'success' : ($product['days_of_inventory'] >= 30 ? 'warning' : 'danger') }}">
                                {{ number_format($product['days_of_inventory'], 1) }}
                            </span>
                        </td>
                        <td class="text-center">
                            @switch($product['stock_status'])
                                @case('in_stock')
                                    <span class="badge bg-success">In Stock</span>
                                    @break
                                @case('low_stock')
                                    <span class="badge bg-warning">Low Stock</span>
                                    @break
                                @case('out_of_stock')
                                    <span class="badge bg-danger">Out of Stock</span>
                                    @break
                            @endswitch
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('modules.products.show', $product['id']) }}" class="btn btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('modules.products.edit', $product['id']) }}" class="btn btn-outline-primary" title="Edit Product">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($product['current_stock'] <= $product['reorder_level'])
                                <a href="{{ route('modules.purchase-orders.create') }}?product_id={{ $product['id'] }}" class="btn btn-outline-warning" title="Create Purchase Order">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($detailedStock->count() === 0)
        <div class="text-center py-5">
            <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No products found</h5>
            <p class="text-muted">Try adjusting your filters or search criteria.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#stockTable').DataTable({
        pageLength: 25,
        order: [[4, 'desc']], // Sort by Current Stock by default
        responsive: true,
        dom: 'Bfrtip',
        buttons: [
            'excel', 'pdf', 'print'
        ]
    });
});

function exportToCSV() {
    let table = document.getElementById('stockTable');
    let rows = table.querySelectorAll('tr');
    let csv = [];

    // Headers
    let headers = [];
    table.querySelectorAll('thead th').forEach(th => {
        headers.push(th.textContent.trim());
    });
    csv.push(headers.join(','));

    // Data rows
    rows.forEach(row => {
        let rowData = [];
        row.querySelectorAll('td').forEach(td => {
            // Remove badges and get text content
            let text = td.textContent.trim();
            rowData.push('"' + text + '"');
        });
        if(rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    // Download CSV
    let csvContent = csv.join('\n');
    let blob = new Blob([csvContent], { type: 'text/csv' });
    let url = window.URL.createObjectURL(blob);
    let a = document.createElement('a');
    a.href = url;
    a.download = 'stock_report_{{ date('Y-m-d') }}.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush