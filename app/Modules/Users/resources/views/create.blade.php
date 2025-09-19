@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Create User</h3>
    </div>
    <form method="POST" action="{{ route('modules.users.store') }}" class="form-minimal" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
        @csrf
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">Password</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-control select2 @error('role') is-invalid @enderror" data-placeholder="— No Role —" data-allow-clear>
                    <option value=""></option>
                    @foreach(($roles ?? []) as $r)
                        <option value="{{ $r->name }}" {{ (string)old('role') === (string)$r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mt-3">
                <label for="avatar" class="form-label">Profile Picture</label>
                <input id="avatar" type="file" name="avatar" accept="image/*" class="form-control @error('avatar') is-invalid @enderror">
                @error('avatar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Optional. JPG, PNG, or WEBP up to 2MB.</div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save
            </button>
            <a href="{{ route('modules.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
