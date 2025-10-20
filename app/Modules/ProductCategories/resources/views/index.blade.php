@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ ucfirst(__('Product Categories')) }}</h1>
    @can('product-categories.create')
    <a href="{{ route('modules.product-categories.create') }}" class="btn btn-primary">Create</a>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="product-categories-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Products</th>
                        <th>Sort Order</th>
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
        new DataTable('#product-categories-table', {
            serverSide: true,
            processing: true,
            ajax: { url: '{{ route('modules.product-categories.data') }}', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'name' },
                { data: 'slug' },
                { data: 'is_active' },
                { data: 'products_count' },
                { data: 'sort_order' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[5, 'asc'], [1, 'asc']],
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
