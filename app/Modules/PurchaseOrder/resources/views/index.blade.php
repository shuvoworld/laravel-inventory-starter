@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Purchase Orders</h1>
    @can('purchase-order.create')
        <a href="{{ route('modules.purchase-order.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Purchase Order
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="purchase-orders-table" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Order Date</th>
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
    new DataTable('#purchase-orders-table', {
        serverSide: true,
        processing: true,
        ajax: {
            url: '{{ route("modules.purchase-order.data") }}',
            dataSrc: 'data'
        },
        columns: [
            { data: 'po_number' },
            { data: 'supplier_name' },
            { data: 'items_count' },
            { data: 'status_badge', orderable: false },
            { data: 'total_amount' },
            { data: 'order_date' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']]
    });
});
</script>
@endpush