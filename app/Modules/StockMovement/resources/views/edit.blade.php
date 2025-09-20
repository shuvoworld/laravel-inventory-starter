@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit {{ ucfirst(__('StockMovement')) }}</h3>
    </div>
    <form method="POST" action="{{ route('modules.stock-movement.update', $item->id) }}">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-rotate me-1"></i> Update
            </button>
            <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<x-audits.table :model="$item" title="Last 10 Audits" />
@endsection
