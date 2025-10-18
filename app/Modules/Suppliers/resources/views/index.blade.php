@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">{{ ucfirst(__('Suppliers')) }}</h1>
    <a href="{{ route('modules.suppliers.create') }}" class="btn btn-primary">Create</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="suppliers-table" class="table table-hover align-middle datatable-minimal table-sm w-100">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Contact Person</th>
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
        const table = new DataTable('#suppliers-table', {
            serverSide: true,
            processing: true,
            ajax: { url: '{{ route('modules.suppliers.data') }}', dataSrc: 'data' },
            columns: [
                { data: 'id' },
                { data: 'code' },
                { data: 'name' },
                { data: 'email' },
                { data: 'phone' },
                { data: 'contact_person' },
                { data: 'status' },
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
