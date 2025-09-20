@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ ucfirst(__('PurchaseOrderItem')) }}</h1>
    @can('purchase-order-item.create')
        <a href="{{ route('modules.purchase-order-item.create') }}" class="btn btn-primary">Create</a>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="purchase-order-item-table" class="table table-hover align-middle datatable-minimal table-sm">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Created</th>
                        <th>Updated</th>
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
        new DataTable('#purchase-order-item-table', {
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('modules.purchase-order-item.data') }}',
                dataSrc: 'data'
            },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'created_at' },
                { data: 'updated_at' },
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
