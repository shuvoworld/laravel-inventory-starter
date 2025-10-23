@extends('layouts.module')

@section('title', 'Manual Stock Correction')
@section('page-title', 'Manual Stock Correction')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-wrench me-2"></i>
            Manual Stock Correction
        </h3>
        <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Stock Movements
        </a>
    </div>
    <form method="POST" action="{{ route('modules.stock-movement.correction.store') }}">
        @csrf
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Manual Stock Correction</strong> allows you to adjust inventory levels when there are discrepancies between physical counts and system records. Use this when you need to:
                <ul class="mb-0 mt-2">
                    <li>Set stock to a specific quantity after physical counting</li>
                    <li>Make small adjustments for discovered discrepancies</li>
                    <li>Correct data entry errors</li>
                </ul>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="product_id" class="form-label">Product *</label>
                    <select id="product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" data-current-stock="{{ $currentStock[$product->id] }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (Current Stock: {{ $currentStock[$product->id] }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="correction_type" class="form-label">Correction Type *</label>
                    <select id="correction_type" name="correction_type" class="form-control @error('correction_type') is-invalid @enderror" required>
                        <option value="">Select Correction Type</option>
                        <option value="set" {{ old('correction_type') == 'set' ? 'selected' : '' }}>Set to Exact Quantity</option>
                        <option value="adjust" {{ old('correction_type') == 'adjust' ? 'selected' : '' }}>Adjust by Quantity</option>
                    </select>
                    @error('correction_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <!-- Set to Exact Quantity Fields -->
                <div id="set-fields" class="col-12">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="target_stock" class="form-label">Target Stock Quantity *</label>
                            <input id="target_stock" type="number" name="target_stock" class="form-control @error('target_stock') is-invalid @enderror" value="{{ old('target_stock') }}" min="0" step="1">
                            @error('target_stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Set the exact quantity you want this product to have in stock.</small>
                        </div>
                    </div>
                </div>

                <!-- Adjust by Quantity Fields -->
                <div id="adjust-fields" class="col-12 d-none">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="adjustment_quantity" class="form-label">Adjustment Quantity *</label>
                            <input id="adjustment_quantity" type="number" name="adjustment_quantity" class="form-control @error('adjustment_quantity') is-invalid @enderror" value="{{ old('adjustment_quantity') }}" step="1">
                            @error('adjustment_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Enter positive number to add stock, negative to remove stock (e.g., +5 or -3).</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <label for="reason" class="form-label">Correction Reason *</label>
                    <select id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                        <option value="">Select Reason</option>
                        <optgroup label="Stock Management">
                            <option value="Opening Balance" {{ old('reason') == 'Opening Balance' ? 'selected' : '' }}>🏪 Opening Balance (Initial Stock Setup)</option>
                            <option value="Physical Count Discrepancy" {{ old('reason') == 'Physical Count Discrepancy' ? 'selected' : '' }}>Physical Count Discrepancy</option>
                            <option value="Lost Items Recovered" {{ old('reason') == 'Lost Items Recovered' ? 'selected' : '' }}>Lost Items Recovered</option>
                        </optgroup>
                        <optgroup label="Error Corrections">
                            <option value="Data Entry Error" {{ old('reason') == 'Data Entry Error' ? 'selected' : '' }}>Data Entry Error</option>
                            <option value="System Error" {{ old('reason') == 'System Error' ? 'selected' : '' }}>System Error</option>
                        </optgroup>
                        <optgroup label="Issues & Losses">
                            <option value="Damaged Goods Found" {{ old('reason') == 'Damaged Goods Found' ? 'selected' : '' }}>Damaged Goods Found</option>
                            <option value="Expired Items Discovered" {{ old('reason') == 'Expired Items Discovered' ? 'selected' : '' }}>Expired Items Discovered</option>
                            <option value="Theft/Loss" {{ old('reason') == 'Theft/Loss' ? 'selected' : '' }}>Theft/Loss</option>
                        </optgroup>
                        <optgroup label="Other">
                            <option value="Supplier Return" {{ old('reason') == 'Supplier Return' ? 'selected' : '' }}>Supplier Return</option>
                            <option value="Quality Control" {{ old('reason') == 'Quality Control' ? 'selected' : '' }}>Quality Control</option>
                            <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                        </optgroup>
                    </select>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label for="notes" class="form-label">Additional Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Provide additional details about this stock correction...">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Stock Preview -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-chart-line me-2"></i>
                                Correction Preview
                            </h6>
                            <div id="correction-preview" class="d-none">
                                <div class="d-flex justify-content-between">
                                    <span>Current Stock:</span>
                                    <strong id="current-stock-display">-</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Correction:</span>
                                    <strong id="correction-display">-</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <span>New Stock:</span>
                                    <strong id="new-stock-display">-</strong>
                                </div>
                            </div>
                            <div id="no-preview" class="text-muted">
                                Select a product and correction details to see preview
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-wrench me-1"></i> Apply Stock Correction
            </button>
            <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const correctionTypeSelect = document.getElementById('correction_type');
    const targetStockInput = document.getElementById('target_stock');
    const adjustmentQuantityInput = document.getElementById('adjustment_quantity');
    const setFields = document.getElementById('set-fields');
    const adjustFields = document.getElementById('adjust-fields');
    const correctionPreview = document.getElementById('correction-preview');
    const noPreview = document.getElementById('no-preview');
    const currentStockDisplay = document.getElementById('current-stock-display');
    const correctionDisplay = document.getElementById('correction-display');
    const newStockDisplay = document.getElementById('new-stock-display');

    function updateCorrectionTypeFields() {
        const correctionType = correctionTypeSelect.value;

        if (correctionType === 'set') {
            setFields.classList.remove('d-none');
            adjustFields.classList.add('d-none');
            targetStockInput.setAttribute('required', 'required');
            adjustmentQuantityInput.removeAttribute('required');
        } else if (correctionType === 'adjust') {
            setFields.classList.add('d-none');
            adjustFields.classList.remove('d-none');
            targetStockInput.removeAttribute('required');
            adjustmentQuantityInput.setAttribute('required', 'required');
        } else {
            setFields.classList.add('d-none');
            adjustFields.classList.add('d-none');
        }

        updatePreview();
    }

    function updatePreview() {
        const selectedOption = productSelect.selectedOptions[0];
        const correctionType = correctionTypeSelect.value;

        if (!selectedOption || !correctionType) {
            correctionPreview.classList.add('d-none');
            noPreview.classList.remove('d-none');
            return;
        }

        const currentStock = parseInt(selectedOption.dataset.currentStock) || 0;
        let correction = 0;
        let newStock = currentStock;

        if (correctionType === 'set') {
            const targetStock = parseInt(targetStockInput.value) || 0;
            correction = targetStock - currentStock;
            newStock = targetStock;

            if (correction > 0) {
                correctionDisplay.innerHTML = '<span class="text-success">+' + correction + '</span>';
            } else if (correction < 0) {
                correctionDisplay.innerHTML = '<span class="text-danger">' + correction + '</span>';
            } else {
                correctionDisplay.innerHTML = '<span class="text-muted">0</span>';
            }
        } else if (correctionType === 'adjust') {
            const adjustmentQuantity = parseInt(adjustmentQuantityInput.value) || 0;
            correction = adjustmentQuantity;
            newStock = currentStock + adjustmentQuantity;

            if (adjustmentQuantity > 0) {
                correctionDisplay.innerHTML = '<span class="text-success">+' + adjustmentQuantity + '</span>';
            } else if (adjustmentQuantity < 0) {
                correctionDisplay.innerHTML = '<span class="text-danger">' + adjustmentQuantity + '</span>';
            } else {
                correctionDisplay.innerHTML = '<span class="text-muted">0</span>';
            }
        }

        currentStockDisplay.textContent = currentStock;

        if (newStock < 0) {
            newStockDisplay.innerHTML = '<span class="text-danger">' + newStock + '</span>';
        } else {
            newStockDisplay.innerHTML = '<span class="text-success">' + newStock + '</span>';
        }

        correctionPreview.classList.remove('d-none');
        noPreview.classList.add('d-none');
    }

    productSelect.addEventListener('change', updatePreview);
    correctionTypeSelect.addEventListener('change', updateCorrectionTypeFields);
    targetStockInput.addEventListener('input', updatePreview);
    adjustmentQuantityInput.addEventListener('input', updatePreview);
});
</script>
@endpush
@endsection