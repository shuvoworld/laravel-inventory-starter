@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Create Stock Adjustment</h3>
    </div>
    <form method="POST" action="{{ route('modules.stock-movement.store') }}">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="product_id" class="form-label">Product *</label>
                    <select id="product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-stock="{{ $product->quantity_on_hand }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Current Stock: {{ $product->quantity_on_hand }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="type" class="form-label">Adjustment Type *</label>
                    <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>Stock In (Increase)</option>
                        <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>Stock Out (Decrease)</option>
                        <option value="adjustment" {{ old('type') == 'adjustment' ? 'selected' : '' }}>Stock Adjustment (Correction)</option>
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="quantity" class="form-label">Quantity *</label>
                    <input id="quantity" type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" min="1" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="reason" class="form-label">Reason *</label>
                    <select id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                        <option value="">Select Reason</option>
                        <option value="Stock Count Correction" {{ old('reason') == 'Stock Count Correction' ? 'selected' : '' }}>Stock Count Correction</option>
                        <option value="Damaged Goods" {{ old('reason') == 'Damaged Goods' ? 'selected' : '' }}>Damaged Goods</option>
                        <option value="Expired Items" {{ old('reason') == 'Expired Items' ? 'selected' : '' }}>Expired Items</option>
                        <option value="Lost/Stolen" {{ old('reason') == 'Lost/Stolen' ? 'selected' : '' }}>Lost/Stolen</option>
                        <option value="Found Items" {{ old('reason') == 'Found Items' ? 'selected' : '' }}>Found Items</option>
                        <option value="Supplier Return" {{ old('reason') == 'Supplier Return' ? 'selected' : '' }}>Supplier Return</option>
                        <option value="Quality Control" {{ old('reason') == 'Quality Control' ? 'selected' : '' }}>Quality Control</option>
                        <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Additional Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Any additional details about this stock adjustment...">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Stock Preview -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Stock Preview</h6>
                            <div id="stock-preview" class="d-none">
                                <div class="d-flex justify-content-between">
                                    <span>Current Stock:</span>
                                    <span id="current-stock">-</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Adjustment:</span>
                                    <span id="adjustment-display">-</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>New Stock:</strong>
                                    <strong id="new-stock">-</strong>
                                </div>
                            </div>
                            <div id="no-preview" class="text-muted">
                                Select a product and enter quantity to see preview
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Create Adjustment
            </button>
            <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const typeSelect = document.getElementById('type');
    const quantityInput = document.getElementById('quantity');
    const stockPreview = document.getElementById('stock-preview');
    const noPreview = document.getElementById('no-preview');
    const currentStockSpan = document.getElementById('current-stock');
    const adjustmentDisplay = document.getElementById('adjustment-display');
    const newStockSpan = document.getElementById('new-stock');

    function updatePreview() {
        const selectedOption = productSelect.selectedOptions[0];
        const type = typeSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;

        if (!selectedOption || !selectedOption.dataset.stock || !type || !quantity) {
            stockPreview.classList.add('d-none');
            noPreview.classList.remove('d-none');
            return;
        }

        const currentStock = parseInt(selectedOption.dataset.stock);
        let adjustment = 0;
        let newStock = currentStock;

        if (type === 'in') {
            adjustment = quantity;
            newStock = currentStock + quantity;
            adjustmentDisplay.innerHTML = '<span class="text-success">+' + quantity + '</span>';
        } else if (type === 'out') {
            adjustment = -quantity;
            newStock = currentStock - quantity;
            adjustmentDisplay.innerHTML = '<span class="text-danger">-' + quantity + '</span>';
        } else if (type === 'adjustment') {
            // For adjustments, the quantity represents the adjustment amount
            adjustment = quantity;
            newStock = currentStock + quantity;
            if (adjustment > 0) {
                adjustmentDisplay.innerHTML = '<span class="text-success">+' + adjustment + '</span>';
            } else if (adjustment < 0) {
                adjustmentDisplay.innerHTML = '<span class="text-danger">' + adjustment + '</span>';
            } else {
                adjustmentDisplay.innerHTML = '<span class="text-muted">0</span>';
            }
        }

        currentStockSpan.textContent = currentStock;
        newStockSpan.textContent = newStock;

        if (newStock < 0) {
            newStockSpan.className = 'text-danger';
        } else {
            newStockSpan.className = '';
        }

        stockPreview.classList.remove('d-none');
        noPreview.classList.add('d-none');
    }

    productSelect.addEventListener('change', updatePreview);
    typeSelect.addEventListener('change', updatePreview);
    quantityInput.addEventListener('input', updatePreview);
});
</script>
@endpush
@endsection
