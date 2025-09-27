@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Operating Expenses</h1>
    <div>
        @can('operating-expenses.view')
            <a href="{{ route('modules.operating-expenses.dashboard') }}" class="btn btn-info me-2">
                <i class="fas fa-chart-pie me-1"></i> Dashboard
            </a>
        @endcan
        @can('operating-expenses.create')
            <a href="{{ route('modules.operating-expenses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Expense
            </a>
        @endcan
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="operating-expenses-table" class="table table-hover align-middle datatable-minimal table-sm">
                <thead>
                    <tr>
                        <th>Expense #</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Frequency</th>
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
        new DataTable('#operating-expenses-table', {
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('modules.operating-expenses.data') }}',
                dataSrc: 'data'
            },
            columns: [
                { data: 'expense_number' },
                { data: 'expense_date' },
                { data: 'category_label' },
                { data: 'description' },
                { data: 'amount' },
                { data: 'payment_status_badge', orderable: false },
                { data: 'frequency_label' },
                { data: 'actions', orderable: false, searchable: false },
            ],
            order: [[0, 'desc']],
            lengthChange: false,
            searching: true,
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