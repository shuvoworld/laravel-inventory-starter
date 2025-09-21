@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Purchase Returns</h1>
    <div class="d-flex gap-2">
        @can('purchase-return.create')
            <a href="{{ route('modules.purchase-return.create') }}" class="btn btn-warning">
                <i class="fas fa-plus me-1"></i> Create Return
            </a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table class="table table-striped" id="dataTable">
            <thead>
                <tr>
                    <th>Return #</th>
                    <th>Purchase Order</th>
                    <th>Supplier</th>
                    <th>Return Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Items</th>
                    <th>Total Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("modules.purchase-return.data") }}',
        columns: [
            {data: 'return_number', name: 'return_number'},
            {data: 'purchase_order_number', name: 'purchaseOrder.po_number'},
            {data: 'supplier_name', name: 'supplier_name'},
            {data: 'return_date', name: 'return_date'},
            {data: 'reason', name: 'reason'},
            {data: 'status_badge', name: 'status', orderable: false, searchable: false},
            {data: 'items_count', name: 'items_count', orderable: false, searchable: false},
            {data: 'total_amount', name: 'total_amount'},
            {data: 'actions', name: 'actions', orderable: false, searchable: false}
        ],
        order: [[3, 'desc']]
    });
});
</script>
@endpush