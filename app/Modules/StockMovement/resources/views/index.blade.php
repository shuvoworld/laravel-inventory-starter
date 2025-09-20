@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Stock Movements</h1>
    @can('stock-movement.create')
        <a href="{{ route('modules.stock-movement.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Stock Adjustment
        </a>
    @endcan
</div>

<!-- Quick Filter Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-arrow-up fa-2x text-success mb-2"></i>
                <h6 class="card-title">Stock In</h6>
                <p class="text-muted small">Purchase Orders & Adjustments</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-arrow-down fa-2x text-danger mb-2"></i>
                <h6 class="card-title">Stock Out</h6>
                <p class="text-muted small">Sales Orders & Adjustments</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-balance-scale fa-2x text-warning mb-2"></i>
                <h6 class="card-title">Adjustments</h6>
                <p class="text-muted small">Manual Stock Corrections</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Stock Movement History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="stock-movement-table" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reference</th>
                        <th>Notes</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#stock-movement-table', {
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("modules.stock-movement.data") }}',
            dataSrc: 'data'
        },
        columns: [
            { data: 'product.name', name: 'product.name' },
            { data: 'type_badge', orderable: false },
            { data: 'quantity_formatted', orderable: false },
            { data: 'reference_info', orderable: false },
            { data: 'notes' },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[5, 'desc']],
        pageLength: 25,
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading stock movements...'
        }
    });
});
</script>
@endpush