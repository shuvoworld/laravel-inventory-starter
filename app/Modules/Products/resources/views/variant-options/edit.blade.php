@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Edit Variant Option</h1>
    <a href="{{ route('modules.products.variant-options.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('modules.products.variant-options.update', $variantOption) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="name" class="form-label">Option Name <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control @error('name') is-invalid @enderror"
                               id="name"
                               name="name"
                               value="{{ old('name', $variantOption->name) }}"
                               placeholder="e.g., Size, Color, Material"
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            The name of this option type (e.g., "Size", "Color", "Material", "Style")
                        </small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="display_order" class="form-label">Display Order</label>
                        <input type="number"
                               class="form-control @error('display_order') is-invalid @enderror"
                               id="display_order"
                               name="display_order"
                               value="{{ old('display_order', $variantOption->display_order) }}"
                               min="0">
                        @error('display_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Order in which this option appears</small>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <h5 class="mb-3">Option Values <span class="text-danger">*</span></h5>
            <p class="text-muted">Add the values for this option (e.g., for "Size": Small, Medium, Large)</p>

            <div id="values-container">
                @if(old('values'))
                    @foreach(old('values') as $index => $value)
                        <div class="value-row mb-2">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value['id'] ?? '' }}">
                                    <input type="text"
                                           class="form-control @error('values.'.$index.'.value') is-invalid @enderror"
                                           name="values[{{ $index }}][value]"
                                           value="{{ $value['value'] ?? '' }}"
                                           placeholder="Value (e.g., Small, Red, Cotton)"
                                           required>
                                    @error('values.'.$index.'.value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <input type="number"
                                           class="form-control"
                                           name="values[{{ $index }}][display_order]"
                                           value="{{ $value['display_order'] ?? $index }}"
                                           placeholder="Order"
                                           min="0">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger w-100" onclick="removeValue(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach($variantOption->values as $index => $value)
                        <div class="value-row mb-2">
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="hidden" name="values[{{ $index }}][id]" value="{{ $value->id }}">
                                    <input type="text"
                                           class="form-control"
                                           name="values[{{ $index }}][value]"
                                           value="{{ $value->value }}"
                                           placeholder="Value (e.g., Small, Red, Cotton)"
                                           required>
                                </div>
                                <div class="col-md-3">
                                    <input type="number"
                                           class="form-control"
                                           name="values[{{ $index }}][display_order]"
                                           value="{{ $value->display_order }}"
                                           placeholder="Order"
                                           min="0">
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger w-100" onclick="removeValue(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="addValue()">
                <i class="fas fa-plus"></i> Add Another Value
            </button>

            <hr class="my-4">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('modules.products.variant-options.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Option
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let valueIndex = {{ old('values') ? count(old('values')) : $variantOption->values->count() }};

function addValue() {
    const container = document.getElementById('values-container');
    const newRow = document.createElement('div');
    newRow.className = 'value-row mb-2';
    newRow.innerHTML = `
        <div class="row">
            <div class="col-md-8">
                <input type="text"
                       class="form-control"
                       name="values[${valueIndex}][value]"
                       placeholder="Value (e.g., Small, Red, Cotton)"
                       required>
            </div>
            <div class="col-md-3">
                <input type="number"
                       class="form-control"
                       name="values[${valueIndex}][display_order]"
                       value="${valueIndex}"
                       placeholder="Order"
                       min="0">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger w-100" onclick="removeValue(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(newRow);
    valueIndex++;
    updateRemoveButtons();
}

function removeValue(button) {
    button.closest('.value-row').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const rows = document.querySelectorAll('.value-row');
    rows.forEach((row, index) => {
        const btn = row.querySelector('.btn-danger');
        if (rows.length === 1) {
            btn.disabled = true;
        } else {
            btn.disabled = false;
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});
</script>
@endpush
