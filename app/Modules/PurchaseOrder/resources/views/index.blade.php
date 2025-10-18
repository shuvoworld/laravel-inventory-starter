@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Purchase Orders</h1>
    @can('purchase-order.create')
        <a href="{{ route('modules.purchase-order.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Purchase Order
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="purchase-orders-table" class="table table-hover align-middle datatable-minimal table-sm">
                <thead>
                    <tr>
                        <th>PO #</th>
                        <th>Supplier</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Paid</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new DataTable('#purchase-orders-table', {
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('modules.purchase-order.data') }}',
                dataSrc: 'data'
            },
            columns: [
                { data: 'po_number' },
                { data: 'supplier_name' },
                { data: 'order_date' },
                { data: 'status_badge', orderable: false },
                { data: 'items_count' },
                { data: 'total_amount' },
                { data: 'paid_amount' },
                { data: 'payment_status_badge', orderable: false },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']],
            lengthChange: false,
            searching: false,
            pagingType: 'simple_numbers',
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
@endsection