@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Create {{ ucfirst(__('Contact')) }}</h3>
    </div>
    <form method="POST" action="{{ route('modules.contact.store') }}" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
        @csrf
        <div class="card-body form-minimal">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input id="phone" type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group">
                <label for="user_id">User</label>
                <select id="user_id" name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror" data-placeholder="— Select user —" data-allow-clear>
                    <option value=""></option>
                    @foreach($users ?? [] as $u)
                        <option value="{{ $u->id }}" {{ (string)old('user_id') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
                @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save
            </button>
            <a href="{{ route('modules.contact.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
