@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Permission Details</h3>
        <div class="d-flex gap-2">
            @can('permissions.edit')
                <a class="btn btn-sm btn-primary" href="{{ route('modules.permissions.edit', $permission->id) }}">
                    <i class="fas fa-pen me-1"></i> Edit
                </a>
            @endcan
            @can('permissions.delete')
                <form method="POST" action="{{ route('modules.permissions.destroy', $permission->id) }}" onsubmit="return confirm('Delete this permission?')">
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
            <dd class="col-sm-9">{{ $permission->id }}</dd>

            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $permission->name }}</dd>

            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">{{ $permission->created_at }}</dd>

            <dt class="col-sm-3">Updated</dt>
            <dd class="col-sm-9">{{ $permission->updated_at }}</dd>
        </dl>
    </div>
    <div class="card-footer">
        <a href="{{ route('modules.permissions.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
