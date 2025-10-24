@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Stock Movements</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('modules.stock-movement.simple-report') }}" class="btn btn-info">
            <i class="fas fa-table me-1"></i> Simple Report
        </a>
        @can('stock-movement.create')
            <a href="{{ route('modules.stock-movement.create') }}" class="btn btn-outline-primary">
                <i class="fas fa-exchange-alt me-1"></i> Stock Adjustment
            </a>
            <a href="{{ route('modules.stock-movement.create') }}?type=purchase" class="btn btn-success">
                <i class="fas fa-arrow-up me-1"></i> Stock In
            </a>
        @endcan
        @can('stock-movement.create')
            <a href="{{ route('modules.stock-movement.correction.create') }}" class="btn btn-warning">
                <i class="fas fa-wrench me-1"></i> Stock Correction
            </a>
        @endcan
        @can('stock-movement.view')
            <a href="{{ route('modules.stock-movement.opening-balance') }}" class="btn btn-primary">
                <i class="fas fa-balance-scale me-1"></i> Opening Balance
            </a>
        @endcan
        @can('stock-movement.view')
            <a href="{{ route('modules.stock-movement.bulk-correction') }}" class="btn btn-outline-info">
                <i class="fas fa-list-alt me-1"></i> Bulk Correction
            </a>
        @endcan
        @can('stock-movement.reconcile')
            <a href="{{ route('modules.stock-movement.reconcile') }}" class="btn btn-outline-secondary">
                <i class="fas fa-sync me-1"></i> Stock Reconciliation
            </a>
        @endcan
        @can('stock-movement.view')
            <a href="{{ route('modules.stock-movement.count-sheet') }}" class="btn btn-outline-dark">
                <i class="fas fa-clipboard-check me-1"></i> Count Sheet
            </a>
        @endcan
    </div>
</div>

<!-- Quick Filter Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-arrow-up fa-2x text-success mb-2"></i>
                <h6 class="card-title">Stock In</h6>
                <p class="text-muted small">Purchases, Returns & Opening Balance</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-arrow-down fa-2x text-danger mb-2"></i>
                <h6 class="card-title">Stock Out</h6>
                <p class="text-muted small">Sales, Returns & Losses</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-wrench fa-2x text-warning mb-2"></i>
                <h6 class="card-title">Corrections</h6>
                <p class="text-muted small">Manual Adjustments & Fixes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-balance-scale fa-2x text-primary mb-2"></i>
                <h6 class="card-title">Opening Balance</h6>
                <p class="text-muted small">Initial Stock Setup</p>
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
            <table id="stock-movement-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
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
        lengthChange: false,
        searching: false,
        pagingType: 'simple_numbers',
        responsive: true,
        language: {
            processing: '<i class="fas fa-spinner fa-spin"></i> Loading stock movements...'
        },
        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        }
    });
});
</script>
@endpush