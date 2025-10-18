@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Expenses</h1>
    @can('expense.create')
        <a href="{{ route('modules.expenses.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Record Expense
        </a>
    @endcan
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="expense-table" class="table table-hover align-middle datatable-minimal table-sm">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Payment Method</th>
                        <th>Amount</th>
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
        new DataTable('#expense-table', {
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route('expenses.data') }}',
                dataSrc: 'data'
            },
            columns: [
                { data: 'expense_date' },
                { data: 'category_name' },
                { data: 'description' },
                { data: 'payment_method' },
                { data: 'amount_formatted' },
                { data: 'status', orderable: false },
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
@endsection