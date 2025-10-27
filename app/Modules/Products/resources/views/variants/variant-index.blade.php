@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Product Variants</h1>
    <a href="{{ route('modules.products.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="btn-group" role="group" aria-label="Variant filters">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="filter-all">All</button>
                <button type="button" class="btn btn-success btn-sm" id="filter-active">Active</button>
                <button type="button" class="btn btn-secondary btn-sm" id="filter-inactive">Inactive</button>
                <button type="button" class="btn btn-warning btn-sm" id="filter-low-stock">Low Stock</button>
                <button type="button" class="btn btn-danger btn-sm" id="filter-out-of-stock">Out of Stock</button>
            </div>
            <div class="d-flex gap-2">
                <select id="product-filter" class="form-select form-select-sm" style="width: 200px;">
                    <option value="">All Products</option>
                    @php
                        $products = \App\Modules\Products\Models\Product::where('store_id', auth()->user()->currentStoreId())
                            ->where('has_variants', true)
                            ->orderBy('name')
                            ->pluck('name', 'id');
                    @endphp
                    @foreach($products as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Search variants..." style="width: 200px;">
            </div>
        </div>
        <div class="table-responsive">
            <table id="variants-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Variant</th>
                        <th>SKU</th>
                        <th>Options</th>
                        <th>Stock Status</th>
                        <th>Stock Level</th>
                        <th>Price</th>
                        <th>Status</th>
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
        const baseUrl = '{{ route('modules.product-variant.data') }}';
        let table;

        // Initialize DataTable
        table = $('#variants-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: baseUrl,
                type: 'GET',
                data: function (d) {
                    d.search = $('#search-input').val();
                    d.product_id = $('#product-filter').val();
                    d.status = getCurrentFilter();
                }
            },
            columns: [
                { data: 'product_name', name: 'product_name' },
                { data: 'display_name', name: 'display_name' },
                { data: 'sku', name: 'sku' },
                {
                    data: 'options',
                    name: 'options',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'stock_status',
                    name: 'stock_status',
                    orderable: false,
                    searchable: false
                },
                { data: 'quantity_on_hand', name: 'quantity_on_hand' },
                { data: 'price', name: 'price' },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'asc'], [1, 'asc']],
            pageLength: 25,
            language: {
                searchPlaceholder: "Search variants...",
                emptyTable: "No variants found",
                processing: '<i class="fas fa-spinner fa-spin me-2"></i>Loading...'
            }
        });

        // Get current filter
        function getCurrentFilter() {
            const activeFilter = $('.btn-group .btn.active').first().attr('id');
            if (activeFilter && activeFilter !== 'filter-all') {
                return activeFilter.replace('filter-', '').replace('-', '_');
            }
            return null;
        }

        // Filter buttons
        $('.btn-group .btn').on('click', function () {
            $('.btn-group .btn').removeClass('active');
            $(this).addClass('active');
            table.ajax.reload();
        });

        // Product filter
        $('#product-filter').on('change', function () {
            table.ajax.reload();
        });

        // Search input with debounce
        let searchTimeout;
        $('#search-input').on('keyup', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function () {
                table.ajax.reload();
            }, 300);
        });

        // Set initial active filter
        $('#filter-all').addClass('active');

        // Handle variant deletion
        $(document).on('click', '.delete-variant', function() {
            const variantId = $(this).data('id');
            const variantName = $(this).closest('tr').find('td:nth-child(2)').text();

            if (confirm(`Are you sure you want to delete variant "${variantName}"? This action cannot be undone.`)) {
                $.ajax({
                    url: `/modules/product-variant/${variantId}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            table.ajax.reload();
                            showAlert(response.message, 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function() {
                        showAlert('Error deleting variant', 'danger');
                    }
                });
            }
        });

        // Alert function
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('.card-body').prepend(alertHtml);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut();
            }, 5000);
        }
    });
</script>
@endpush
@endsection