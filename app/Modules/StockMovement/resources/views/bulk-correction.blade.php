@extends('layouts.module')

@section('title', 'Bulk Stock Correction')
@section('page-title', 'Bulk Stock Correction')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-list-alt me-2"></i>
            Bulk Stock Correction
        </h3>
        <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Stock Movements
        </a>
    </div>

    <form method="POST" action="{{ route('modules.stock-movement.correction.store') }}">
        @csrf
        <div class="card-body">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Bulk Stock Correction</strong> allows you to make rapid adjustments to multiple products at once. This is ideal for:
                <ul class="mb-0 mt-2">
                    <li>Physical inventory count adjustments</li>
                    <li>System error corrections affecting multiple products</li>
                    <li>Batch processing of discovered discrepancies</li>
                    <li>Quick fixes for multiple data entry errors</li>
                </ul>
            </div>

            <!-- Correction Settings -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="correction_type" class="form-label">Correction Type *</label>
                    <select id="correction_type" name="correction_type" class="form-control @error('correction_type') is-invalid @enderror" required>
                        <option value="">Select Correction Type</option>
                        <option value="set" {{ old('correction_type') == 'set' ? 'selected' : '' }}>Set to Exact Quantities</option>
                        <option value="adjust" {{ old('correction_type') == 'adjust' ? 'selected' : '' }}>Adjust by Quantities</option>
                    </select>
                    @error('correction_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="reason" class="form-label">Correction Reason *</label>
                    <select id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                        <option value="">Select Reason</option>
                        <optgroup label="Inventory Management">
                            <option value="Physical Count Discrepancy" {{ old('reason') == 'Physical Count Discrepancy' ? 'selected' : '' }}>Physical Count Discrepancy</option>
                            <option value="System Data Error" {{ old('reason') == 'System Data Error' ? 'selected' : '' }}>System Data Error</option>
                            <option value="Batch Data Entry Error" {{ old('reason') == 'Batch Data Entry Error' ? 'selected' : '' }}>Batch Data Entry Error</option>
                        </optgroup>
                        <optgroup label="Quality Control">
                            <option value="Quality Inspection Results" {{ old('reason') == 'Quality Inspection Results' ? 'selected' : '' }}>Quality Inspection Results</option>
                            <option value="Damaged Goods Found" {{ old('reason') == 'Damaged Goods Found' ? 'selected' : '' }}>Damaged Goods Found</option>
                            <option value="Expired Items Discovered" {{ old('reason') == 'Expired Items Discovered' ? 'selected' : '' }}>Expired Items Discovered</option>
                        </optgroup>
                        <optgroup label="Other">
                            <option value="Warehouse Transfer Adjustment" {{ old('reason') == 'Warehouse Transfer Adjustment' ? 'selected' : '' }}>Warehouse Transfer Adjustment</option>
                            <option value="System Upgrade Adjustment" {{ old('reason') == 'System Upgrade Adjustment' ? 'selected' : '' }}>System Upgrade Adjustment</option>
                            <option value="Other" {{ old('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                        </optgroup>
                    </select>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Bulk Product Selection -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all-products" class="form-check-input">
                            </th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Current Stock</th>
                            <th id="correction-column-header">Correction Quantity</th>
                            <th style="width: 150px;">Preview</th>
                        </tr>
                    </thead>
                    <tbody id="bulk-correction-table-body">
                        @foreach($products as $index => $product)
                            <tr data-product-id="{{ $product->id }}" data-index="{{ $index }}">
                                <td>
                                    <input type="checkbox" name="selected_products[]"
                                           class="form-check-input product-checkbox"
                                           value="{{ $product->id }}"
                                           data-current-stock="{{ $currentStock[$product->id] }}"
                                           data-product-name="{{ $product->name }}">
                                </td>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->category)
                                        <br><small class="text-muted">{{ $product->category->name }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->sku ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $currentStock[$product->id] ?? 0 }}</span>
                                </td>
                                <td>
                                    <input type="number"
                                           name="correction_quantities[{{ $product->id }}]"
                                           class="form-control correction-quantity"
                                           data-current-stock="{{ $currentStock[$product->id] ?? 0 }}"
                                           data-product-name="{{ $product->name }}"
                                           step="1"
                                           placeholder="0">
                                </td>
                                <td class="preview-cell">
                                    <div class="preview-container">
                                        <small class="text-muted">Select product</small>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Additional Notes -->
            <div class="row mt-4">
                <div class="col-12">
                    <label for="notes" class="form-label">Additional Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                              rows="3" placeholder="Provide additional details about this bulk correction...">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-check-square fa-2x text-success mb-2"></i>
                            <h6 class="card-title">Selected Products</h6>
                            <h5 class="text-success mb-0" id="selected-products-count">0</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-arrow-up fa-2x text-info mb-2"></i>
                            <h6 class="card-title">Total Increase</h6>
                            <h5 class="text-info mb-0" id="total-increase">0</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-arrow-down fa-2x text-danger mb-2"></i>
                            <h6 class="card-title">Total Decrease</h6>
                            <h5 class="text-danger mb-0" id="total-decrease">0</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <h6 class="card-title">Changes Required</h6>
                            <h5 class="text-warning mb-0" id="changes-count">0</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <div class="d-flex gap-2">
                <button type="button" id="clear-all-corrections" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear All
                </button>
                <button type="button" id="set-zero-corrections" class="btn btn-outline-info">
                    <i class="fas fa-ban me-1"></i> Set to Zero
                </button>
            </div>
            <button type="submit" class="btn btn-warning" id="submit-bulk-correction">
                <i class="fas fa-wrench me-1"></i> Apply Bulk Corrections
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-products');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const correctionTypeSelect = document.getElementById('correction_type');
    const correctionQuantityInputs = document.querySelectorAll('.correction-quantity');
    const correctionColumnHeader = document.getElementById('correction-column-header');
    const submitButton = document.getElementById('submit-bulk-correction');

    // Statistics elements
    const selectedCountElement = document.getElementById('selected-products-count');
    const totalIncreaseElement = document.getElementById('total-increase');
    const totalDecreaseElement = document.getElementById('total-decrease');
    const changesCountElement = document.getElementById('changes-count');

    function updateColumnHeader() {
        const correctionType = correctionTypeSelect.value;
        if (correctionType === 'set') {
            correctionColumnHeader.textContent = 'Target Stock';
        } else {
            correctionColumnHeader.textContent = 'Adjustment Quantity';
        }
    }

    function updateStatistics() {
        let selectedCount = 0;
        let totalIncrease = 0;
        let totalDecrease = 0;
        let changesCount = 0;

        productCheckboxes.forEach((checkbox, index) => {
            if (checkbox.checked) {
                selectedCount++;
                const quantityInput = correctionQuantityInputs[index];
                const currentStock = parseInt(checkbox.dataset.currentStock) || 0;

                if (correctionTypeSelect.value === 'set') {
                    const targetStock = parseInt(quantityInput.value) || 0;
                    const difference = targetStock - currentStock;

                    if (difference > 0) {
                        totalIncrease += difference;
                    } else if (difference < 0) {
                        totalDecrease += Math.abs(difference);
                    }

                    if (targetStock !== currentStock) {
                        changesCount++;
                    }
                } else {
                    const adjustment = parseInt(quantityInput.value) || 0;

                    if (adjustment > 0) {
                        totalIncrease += adjustment;
                    } else if (adjustment < 0) {
                        totalDecrease += Math.abs(adjustment);
                    }

                    if (adjustment !== 0) {
                        changesCount++;
                    }
                }

                // Enable/disable quantity input based on checkbox
                quantityInput.disabled = !checkbox.checked;

                // Update preview cell
                updatePreviewCell(quantityInput, checkbox);
            }
        });

        selectedCountElement.textContent = selectedCount;
        totalIncreaseElement.textContent = totalIncrease;
        totalDecreaseElement.textContent = totalDecrease;
        changesCountElement.textContent = changesCount;

        // Enable/disable submit button
        submitButton.disabled = selectedCount === 0;
    }

    function updatePreviewCell(quantityInput, checkbox) {
        const row = quantityInput.closest('tr');
        const previewCell = row.querySelector('.preview-container');
        const currentStock = parseInt(checkbox.dataset.currentStock) || 0;
        const productName = checkbox.dataset.productName;

        if (!checkbox.checked) {
            previewCell.innerHTML = '<small class="text-muted">Select product</small>';
            return;
        }

        const quantityValue = parseInt(quantityInput.value) || 0;
        let previewHtml = '';

        if (correctionTypeSelect.value === 'set') {
            const targetStock = quantityValue;
            const difference = targetStock - currentStock;

            let changeHtml = '';
            if (difference > 0) {
                changeHtml = `<span class="text-success">+${difference}</span>`;
            } else if (difference < 0) {
                changeHtml = `<span class="text-danger">${difference}</span>`;
            } else {
                changeHtml = '<span class="text-muted">No change</span>';
            }

            previewHtml = `
                <div>
                    <small class="text-muted">Target: ${targetStock}</small><br>
                    <small>Change: ${changeHtml}</small>
                </div>
            `;
        } else {
            const adjustment = quantityValue;

            let adjustmentHtml = '';
            if (adjustment > 0) {
                adjustmentHtml = `<span class="text-success">+${adjustment}</span>`;
            } else if (adjustment < 0) {
                adjustmentHtml = `<span class="text-danger">${adjustment}</span>`;
            } else {
                adjustmentHtml = '<span class="text-muted">0</span>';
            }

            const newStock = currentStock + adjustment;
            previewHtml = `
                <div>
                    <small>Adjust: ${adjustmentHtml}</small><br>
                    <small class="text-muted">New: ${newStock}</small>
                </div>
            `;
        }

        previewCell.innerHTML = previewHtml;
    }

    function setAllQuantities(value = 0) {
        correctionQuantityInputs.forEach((input, index) => {
            const checkbox = productCheckboxes[index];
            if (checkbox.checked) {
                if (correctionTypeSelect.value === 'set') {
                    input.value = value; // Set to target value
                } else {
                    input.value = value; // Set adjustment value
                }
                updatePreviewCell(input, checkbox);
            }
        });
        updateStatistics();
    }

    // Event listeners
    selectAllCheckbox.addEventListener('change', function() {
        productCheckboxes.forEach((checkbox, index) => {
            checkbox.checked = selectAllCheckbox.checked;
            correctionQuantityInputs[index].disabled = !selectAllCheckbox.checked;

            if (selectAllCheckbox.checked) {
                updatePreviewCell(correctionQuantityInputs[index], checkbox);
            }
        });
        updateStatistics();
    });

    correctionTypeSelect.addEventListener('change', function() {
        updateColumnHeader();
        // Clear all inputs when type changes
        correctionQuantityInputs.forEach(input => input.value = 0);
        updateStatistics();
    });

    productCheckboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', function() {
            updateStatistics();
        });
    });

    correctionQuantityInputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            const checkbox = productCheckboxes[index];
            // Auto-check checkbox if quantity is entered
            if (parseInt(input.value) !== 0) {
                checkbox.checked = true;
            }
            updateStatistics();
        });
    });

    document.getElementById('clear-all-corrections').addEventListener('click', function() {
        setAllQuantities(0);
    });

    document.getElementById('set-zero-corrections').addEventListener('click', function() {
        productCheckboxes.forEach((checkbox, index) => {
            checkbox.checked = true;
            const quantityInput = correctionQuantityInputs[index];

            if (correctionTypeSelect.value === 'set') {
                quantityInput.value = 0;
            } else {
                const currentStock = parseInt(checkbox.dataset.currentStock) || 0;
                quantityInput.value = -currentStock; // Adjustment to reach zero
            }

            updatePreviewCell(quantityInput, checkbox);
        });
        updateStatistics();
    });

    // Initialize
    updateColumnHeader();
    updateStatistics();
});
</script>
@endpush
@endsection