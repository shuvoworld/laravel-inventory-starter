@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Stock Reorder Recommendations</h1>
    <a href="{{ route('modules.reports.stock') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Stock Report
    </a>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h4>{{ $recommendations->where('urgency', 'critical')->count() }}</h4>
                <p class="card-text mb-0">Critical Items</p>
                <small>Out of stock</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ $recommendations->where('urgency', 'high')->count() }}</h4>
                <p class="card-text mb-0">High Priority</p>
                <small>Very low stock</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>{{ $recommendations->where('urgency', 'medium')->count() }}</h4>
                <p class="card-text mb-0">Medium Priority</p>
                <small>Below reorder level</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>${{ number_format($recommendations->sum('estimated_cost'), 2) }}</h4>
                <p class="card-text mb-0">Total Investment</p>
                <small>Recommended orders</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-danger w-100" onclick="filterByUrgency('critical')">
                    <i class="fas fa-exclamation-circle me-1"></i> Show Critical Only
                </button>
            </div>
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-warning w-100" onclick="filterByUrgency('high')">
                    <i class="fas fa-exclamation-triangle me-1"></i> Show High Priority
                </button>
            </div>
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-info w-100" onclick="filterByUrgency('medium')">
                    <i class="fas fa-info-circle me-1"></i> Show Medium Priority
                </button>
            </div>
            <div class="col-md-3 mb-2">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="showAll()">
                    <i class="fas fa-list me-1"></i> Show All
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reorder Recommendations Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Recommended Reorder List</h5>
        <div>
            <button type="button" class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="generatePurchaseOrders()">
                <i class="fas fa-shopping-cart me-1"></i> Create POs
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="reorderTable">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 20px;">
                            <input type="checkbox" id="selectAll" onchange="toggleAll()">
                        </th>
                        <th>Product Details</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-center">Monthly Usage</th>
                        <th class="text-center">Recommended Qty</th>
                        <th class="text-end">Unit Cost</th>
                        <th class="text-end">Total Cost</th>
                        <th class="text-center">Urgency</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recommendations as $item)
                    <tr data-urgency="{{ $item['urgency'] }}">
                        <td>
                            <input type="checkbox" class="product-checkbox" value="{{ $item['id'] }}"
                                   data-product="{{ json_encode($item) }}">
                        </td>
                        <td>
                            <div class="fw-bold">{{ $item['name'] }}</div>
                            <div class="small text-muted">
                                SKU: {{ $item['sku'] }} |
                                Brand: {{ $item['brand'] }}
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $item['current_stock'] <= 0 ? 'danger' : 'warning' }} fs-6">
                                {{ $item['current_stock'] }}
                            </span>
                        </td>
                        <td class="text-center">{{ $item['reorder_level'] }}</td>
                        <td class="text-center">{{ number_format($item['monthly_consumption'], 1) }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary fs-6">{{ $item['recommended_quantity'] }}</span>
                        </td>
                        <td class="text-end">
                            @php
                            $unitCost = App\Modules\Products\Models\Product::find($item['id'])->cost_price ?? 0;
                            @endphp
                            ${{ number_format($unitCost, 2) }}
                        </td>
                        <td class="text-end fw-bold">
                            ${{ number_format($item['estimated_cost'], 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $item['urgency'] === 'critical' ? 'danger' : ($item['urgency'] === 'high' ? 'warning' : 'info') }} fs-6">
                                {{ ucfirst($item['urgency']) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('modules.products.show', $item['id']) }}" class="btn btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('modules.purchase-orders.create') }}?product_id={{ $item['id'] }}&quantity={{ $item['recommended_quantity'] }}"
                                   class="btn btn-outline-primary" title="Quick Purchase Order">
                                    <i class="fas fa-shopping-cart"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary">
                    <tr class="fw-bold">
                        <td colspan="8">Total Investment for Selected Items:</td>
                        <td colspan="3" class="text-end" id="selectedTotal">$0.00</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($recommendations->count() === 0)
        <div class="text-center py-5">
            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
            <h5 class="text-muted">All Stock Levels Healthy</h5>
            <p class="text-muted">Great job! All products are above their reorder levels.</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });

    updateSelectedTotal();
}

function updateSelectedTotal() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');
    let total = 0;

    checkboxes.forEach(checkbox => {
        const product = JSON.parse(checkbox.dataset.product);
        total += product.estimated_cost;
    });

    document.getElementById('selectedTotal').textContent = '$' + total.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Add event listeners to checkboxes
document.querySelectorAll('.product-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedTotal);
});

function filterByUrgency(urgency) {
    const rows = document.querySelectorAll('#reorderTable tbody tr');

    rows.forEach(row => {
        if (row.dataset.urgency === urgency) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function showAll() {
    const rows = document.querySelectorAll('#reorderTable tbody tr');
    rows.forEach(row => {
        row.style.display = '';
    });
}

function exportToCSV() {
    const table = document.getElementById('reorderTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];

    // Headers
    const headers = ['Product', 'SKU', 'Brand', 'Current Stock', 'Reorder Level',
                    'Monthly Usage', 'Recommended Qty', 'Unit Cost', 'Total Cost', 'Urgency'];
    csv.push(headers.join(','));

    // Data rows
    document.querySelectorAll('#reorderTable tbody tr').forEach(row => {
        const checkbox = row.querySelector('.product-checkbox');
        if (checkbox) {
            const product = JSON.parse(checkbox.dataset.product);
            const rowData = [
                `"${product.name}"`,
                `"${product.sku}"`,
                `"${product.brand}"`,
                product.current_stock,
                product.reorder_level,
                product.monthly_consumption,
                product.recommended_quantity,
                product.unit_cost || 0,
                product.estimated_cost,
                product.urgency
            ];
            csv.push(rowData.join(','));
        }
    });

    // Download CSV
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reorder_recommendations_{{ date('Y-m-d') }}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}

function generatePurchaseOrders() {
    const checkboxes = document.querySelectorAll('.product-checkbox:checked');

    if (checkboxes.length === 0) {
        alert('Please select at least one product to create purchase orders.');
        return;
    }

    const selectedProducts = [];
    checkboxes.forEach(checkbox => {
        selectedProducts.push(JSON.parse(checkbox.dataset.product));
    });

    // Group by supplier
    const groupedBySupplier = selectedProducts.reduce((groups, product) => {
        const supplier = product.supplier || 'Default Supplier';
        if (!groups[supplier]) {
            groups[supplier] = [];
        }
        groups[supplier].push(product);
        return groups;
    }, {});

    // Create a modal or navigate to purchase order creation
    let confirmMessage = `You will create purchase orders for:\n\n`;
    Object.keys(groupedBySupplier).forEach(supplier => {
        confirmMessage += `${supplier}: ${groupedBySupplier[supplier].length} products\n`;
        const total = groupedBySupplier[supplier].reduce((sum, p) => sum + p.estimated_cost, 0);
        confirmMessage += `  Total: $${total.toFixed(2)}\n\n`;
    });

    if (confirm(confirmMessage + 'Proceed with creating purchase orders?')) {
        // Redirect to first supplier's purchase order creation
        const firstSupplier = Object.keys(groupedBySupplier)[0];
        const firstProduct = groupedBySupplier[firstSupplier][0];
        window.location.href = `/modules/purchase-orders/create?supplier=${encodeURIComponent(firstSupplier)}&products=${encodeURIComponent(JSON.stringify(groupedBySupplier[firstSupplier]))}`;
    }
}
</script>
@endpush