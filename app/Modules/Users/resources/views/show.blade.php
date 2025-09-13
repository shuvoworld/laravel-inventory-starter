@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">User Details</h3>
        <div class="d-flex gap-2">
            @can('users.edit')
                <a class="btn btn-sm btn-primary" href="{{ route('modules.users.edit', $user->id) }}">
                    <i class="fas fa-pen me-1"></i> Edit
                </a>
            @endcan
            @can('users.delete')
                <form method="POST" action="{{ route('modules.users.destroy', $user->id) }}" onsubmit="return confirm('Delete this user?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">{{ $user->id }}</dd>

            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $user->name }}</dd>

            <dt class="col-sm-3">Email</dt>
            <dd class="col-sm-9">{{ $user->email }}</dd>

            <dt class="col-sm-3">Roles</dt>
            <dd class="col-sm-9">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</dd>

            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">{{ $user->created_at }}</dd>

            <dt class="col-sm-3">Updated</dt>
            <dd class="col-sm-9">{{ $user->updated_at }}</dd>
        </dl>
    </div>
    <div class="card-footer">
        <a href="{{ route('modules.users.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
