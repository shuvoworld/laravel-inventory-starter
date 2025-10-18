@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Create {{ ucfirst(__('ProductAttribute')) }}</h3>
    </div>
    <form method="POST" action="{{ route('modules.product-attribute.store') }}" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
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
                <label for="attribute_set_id" class="form-label">Attribute Set</label>
                <select id="attribute_set_id" name="attribute_set_id" class="form-select @error('attribute_set_id') is-invalid @enderror">
                    <option value="">-- Select Attribute Set --</option>
                    @foreach($attributeSets as $set)
                        <option value="{{ $set->id }}" {{ old('attribute_set_id') == $set->id ? 'selected' : '' }}>
                            {{ $set->name }}
                        </option>
                    @endforeach
                </select>
                @error('attribute_set_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save
            </button>
            <a href="{{ route('modules.product-attribute.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
