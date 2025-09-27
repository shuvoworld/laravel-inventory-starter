@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit {{ ucfirst(__('Product')) }}</h3>
    </div>
    <form method="POST" action="{{ route('modules.products.update', $item->id) }}" onsubmit="this.querySelector('button[type=submit]').disabled=true;">
        @csrf
        @method('PUT')
        <div class="card-body form-minimal">
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <label for="sku" class="form-label">SKU</label>
                    <input id="sku" type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $item->sku) }}">
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="name" class="form-label">Name</label>
                    <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $item->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="unit" class="form-label">Unit</label>
                    <input id="unit" type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $item->unit) }}" placeholder="pcs, box, bottle">
                    @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="price" class="form-label">Selling Price</label>
                    <input id="price" type="number" step="0.01" min="0" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $item->price) }}">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="cost_price" class="form-label">Cost Price</label>
                    <input id="cost_price" type="number" step="0.01" min="0" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', $item->cost_price) }}">
                    @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label for="reorder_level" class="form-label">Reorder Level</label>
                    <input id="reorder_level" type="number" min="0" name="reorder_level" class="form-control @error('reorder_level') is-invalid @enderror" value="{{ old('reorder_level', $item->reorder_level) }}">
                    @error('reorder_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">On Hand</label>
                    <input type="number" class="form-control" value="{{ $item->quantity_on_hand }}" disabled>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label">Profit Margin</label>
                    <input type="number" class="form-control" value="{{ number_format($item->getProfitMargin(), 2) }}%" disabled>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button class="btn btn-primary" type="submit">
                <i class="fas fa-rotate me-1"></i> Update
            </button>
            <a href="{{ route('modules.products.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
