@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ ucfirst(__('Products')) }}</h1>
    <a href="{{ route('modules.products.create') }}" class="btn btn-primary">Create</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="products-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Unit</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Profit Margin</th>
                        <th>On Hand</th>
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
        new DataTable('#products-table', {
            serverSide: true,
            processing: true,
            ajax: { url: '{{ route('modules.products.data') }}', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'sku' },
                { data: 'name' },
                { data: 'unit' },
                { data: 'cost_price' },
                { data: 'price' },
                { data: 'profit_margin' },
                { data: 'quantity_on_hand' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']],
            lengthChange: false,
            searching: false,
            pageLength: 10,
            pagingType: 'simple_numbers',
            layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' }
        });
    });
</script>
@endpush
@endsection
