@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit User</h3>
    </div>
    <form method="POST" action="{{ route('modules.users.update', $user->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="password" class="form-label">New Password</label>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Leave blank to keep current password.</div>
                </div>
                <div class="col-md-6">
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                @php($selectedRole = old('role', $user->roles->pluck('name')->first()))
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-control select2 @error('role') is-invalid @enderror" data-placeholder="— No Role —" data-allow-clear>
                    <option value=""></option>
                    @foreach(($roles ?? []) as $r)
                        <option value="{{ $r->name }}" {{ (string)$selectedRole === (string)$r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mt-3">
                <label for="avatar" class="form-label">Profile Picture</label>
                @php($photo = $user->profile_photo_path ?? null)
                @if($photo)
                    <div class="mb-2">
                        <img src="{{ asset('storage/' . $photo) }}" alt="Current profile picture" class="rounded" style="max-width: 96px; max-height: 96px; object-fit: cover;">
                    </div>
                @endif
                <input id="avatar" type="file" name="avatar" accept="image/*" class="form-control @error('avatar') is-invalid @enderror">
                @error('avatar')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Optional. Upload to replace current. JPG, PNG, or WEBP up to 2MB.</div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-rotate me-1"></i> Update
            </button>
            <a href="{{ route('modules.users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<x-audits.table :model="$user" title="Last 10 Audits" />
@endsection
