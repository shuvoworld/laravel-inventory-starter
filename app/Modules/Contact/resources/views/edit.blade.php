@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit {{ ucfirst(__('Contact')) }}</h3>
    </div>
    <form method="POST" action="{{ route('modules.contact.update', $item->id) }}" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
        @csrf
        @method('PUT')
        <div class="card-body form-minimal">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $item->email) }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input id="phone" type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $item->phone) }}">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <x-form.select
                    name="user_id"
                    label="User"
                    model="App\\Models\\User"
                    optionValue="id"
                    optionLabel="name"
                    :selected="old('user_id', $item->user_id)"
                    includeEmpty="true"
                    select2="true"
                />
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-rotate me-1"></i> Update
            </button>
            <a href="{{ route('modules.contact.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<x-audits.table :model="$item" title="Last 10 Audits" />
@endsection
