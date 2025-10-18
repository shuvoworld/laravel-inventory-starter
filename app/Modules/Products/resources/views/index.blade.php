@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ ucfirst(__('Products')) }}</h1>
    <a href="{{ route('modules.products.create') }}" class="btn btn-primary">Create</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            @php $lowStockCount = \App\Modules\Products\Models\Product::lowStock()->count(); @endphp
            <div class="btn-group" role="group" aria-label="Product filters">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="filter-all">All</button>
                <button type="button" class="btn btn-outline-warning btn-sm" id="filter-low-stock">
                    Low Stock
                    @if($lowStockCount > 0)
                        <span class="badge bg-danger ms-1">{{ $lowStockCount }}</span>
                    @endif
                </button>
            </div>
            <div></div>
        </div>
        <div class="table-responsive">
            <table id="products-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
                <thead>
                    <tr>
                        <th>Image</th>
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
        const baseUrl = '{{ route('modules.products.data') }}';
        const table = new DataTable('#products-table', {
            serverSide: true,
            processing: true,
            ajax: { url: baseUrl, dataSrc: 'data' },
            columns: [
                {
                    data: 'image',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        const imageUrl = data ? '{{ asset('storage') }}/' + data : '{{ asset('images/product-placeholder.svg') }}';
                        return `<img src="${imageUrl}" alt="${row.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;" loading="lazy">`;
                    }
                },
                { data: 'sku' },
                { data: 'name' },
                { data: 'unit' },
                { data: 'cost_price' },
                { data: 'price' },
                { data: 'profit_margin' },
                { data: 'quantity_on_hand' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[1, 'desc']],
            lengthChange: false,
            searching: false,
            pageLength: 10,
            pagingType: 'simple_numbers',
            layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' }
        });

        function setFilter(filter) {
            const url = filter === 'low-stock' ? `${baseUrl}?filter=low-stock` : baseUrl;
            table.ajax.url(url).load();
        }
        document.getElementById('filter-all').addEventListener('click', function(){ setFilter('all'); });
        document.getElementById('filter-low-stock').addEventListener('click', function(){ setFilter('low-stock'); });
    });
</script>
@endpush
@endsection
