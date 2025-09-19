@extends('layouts.adminlte')

@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))

@section('content')
<div class="row">
    @can('users.view')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ \App\Models\User::count() }}</h3>
                <p>{{ __('Users') }}</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('modules.users.index') }}" class="small-box-footer">{{ __('Manage users') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan


    @role('admin')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ \App\Models\Module::count() }}</h3>
                <p>{{ __('Modules') }}</p>
            </div>
            <div class="icon"><i class="fas fa-cubes"></i></div>
            <a href="{{ route('admin.modules.index') }}" class="small-box-footer">{{ __('Manage modules') }} <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endrole
</div>

<div class="row">
    @can('users.view')
    <div class="col-lg-6">
        <div class="card card-primary card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">{{ __('Recent Users') }}</h3>
                <div class="card-tools">
                    <a href="{{ route('modules.users.index') }}" class="btn btn-tool">{{ __('View all') }}</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="dashboard-users-table" class="table table-striped table-hover table-sm mb-0 w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('Roles') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endcan

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const usersEl = document.querySelector('#dashboard-users-table');
        if (usersEl) {
            new DataTable(usersEl, {
                serverSide: true,
                processing: true,
                ajax: { url: '{{ route('modules.users.data') }}', dataSrc: 'data' },
                columns: [
                    { data: 'id' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'roles' },
                    { data: 'actions', orderable: false, searchable: false },
                ],
                order: [[0, 'desc']],
                lengthChange: false,
                searching: false,
                pageLength: 5,
                pagingType: 'simple_numbers',
                layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' }
            });
        }

    });
</script>
@endpush
