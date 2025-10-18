@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Sales Orders</h1>
    @can('sales-order.create')
        <a href="{{ route('modules.sales-order.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Sales Order
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="sales-order-table" class="table table-hover align-middle datatable-minimal table-sm">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Items</th>
                        <th>Total</th>
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
        new DataTable('#sales-order-table', {
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('modules.sales-order.data') }}',
                dataSrc: 'data'
            },
            columns: [
                { data: 'order_number' },
                { data: 'customer_name' },
                { data: 'order_date' },
                { data: 'status_badge', orderable: false },
                { data: 'payment_status_badge', orderable: false },
                { data: 'items_count' },
                { data: 'total_amount' },
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
