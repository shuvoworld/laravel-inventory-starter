@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Expense Categories</h1>
    @can('expense-category.create')
        <a href="{{ route('modules.expense-category.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Create Expense Category
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="expense-category-table" class="table table-hover align-middle datatable-minimal table-sm">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Color</th>
                        <th>Status</th>
                        <th>Created</th>
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
        new DataTable('#expense-category-table', {
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('modules.expense-category.data') }}',
                dataSrc: 'data'
            },
            columns: [
                { data: 'name' },
                { data: 'description' },
                { data: 'color' },
                { data: 'status', orderable: false },
                { data: 'created_at' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[0, 'asc']],
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