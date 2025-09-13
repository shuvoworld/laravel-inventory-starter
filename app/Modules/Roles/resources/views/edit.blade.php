@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit Role</h3>
    </div>
    <form method="POST" action="{{ route('modules.roles.update', $role->id) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-4">
                <h5 class="mb-2">Permissions</h5>
                <div class="row">
                    @php($oldPerms = old('permissions', $selected ?? []))
                    @forelse($groupedPermissions as $module => $perms)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-2 h-100">
                                <div class="fw-semibold text-muted mb-2">{{ $module ?: 'general' }}</div>
                                <div class="d-flex flex-column gap-1">
                                    @foreach($perms as $perm)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm-{{ md5($perm->name) }}" @checked(in_array($perm->name, $oldPerms))>
                                            <label class="form-check-label" for="perm-{{ md5($perm->name) }}">{{ $perm->name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">No permissions defined yet.</div>
                    @endforelse
                </div>
                @error('permissions')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-rotate me-1"></i> Update
            </button>
            <a href="{{ route('modules.roles.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
