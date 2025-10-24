@extends('layouts.module')

@section('title', 'Opening Balance Setup')
@section('page-title', 'Opening Balance Setup')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-balance-scale me-2"></i>
            Opening Balance Setup
        </h3>
        <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Stock Movements
        </a>
    </div>

    <form method="POST" action="{{ route('modules.stock-movement.opening-balance.store') }}">
        @csrf
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Opening Balance Setup</strong> allows you to set initial stock quantities for your inventory. This is typically done when:
                <ul class="mb-0 mt-2">
                    <li>Setting up a new store location</li>
                    <li>Migrating from another inventory system</li>
                    <li>Starting inventory tracking for existing products</li>
                    <li>Performing a complete inventory reset</li>
                </ul>
            </div>

            <!-- Opening Balance Settings -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="opening_date" class="form-label">Opening Date *</label>
                    <input id="opening_date" type="date" name="opening_date"
                           class="form-control @error('opening_date') is-invalid @enderror"
                           value="{{ old('opening_date', now()->format('Y-m-d')) }}"
                           required>
                    @error('opening_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Date when opening balance should be recorded.</small>
                </div>
                <div class="col-md-8">
                    <label for="notes" class="form-label">Opening Balance Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror"
                              rows="2" placeholder="Reason for opening balance setup...">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <!-- Products Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="select-all" class="form-check-input">
                            </th>
                            <th>Product</th>
                            <th>SKU</th>
                            <th>Current Stock</th>
                            <th>Opening Quantity *</th>
                            <th style="width: 150px;">Preview</th>
                        </tr>
                    </thead>
                    <tbody id="products-table-body">
                        @foreach($products as $index => $product)
                            <tr data-product-id="{{ $product->id }}" data-index="{{ $index }}">
                                <td>
                                    <input type="checkbox" name="products[{{ $index }}][selected]"
                                           class="form-check-input product-checkbox" value="1">
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
                                    <input type="number" name="products[{{ $index }}][opening_quantity]"
                                           class="form-control opening-quantity"
                                           data-current-stock="{{ $currentStock[$product->id] ?? 0 }}"
                                           data-product-name="{{ $product->name }}"
                                           min="0" step="1" value="{{ old('products.' . $index . '.opening_quantity') ?? 0 }}"
                                           placeholder="0">
                                    <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $product->id }}">
                                </td>
                                <td class="stock-preview-cell">
                                    <div class="preview-container">
                                        <small class="text-muted">Enter quantity</small>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Summary Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-box fa-2x text-primary mb-2"></i>
                            <h6 class="card-title">Total Products</h6>
                            <h5 class="text-primary mb-0" id="total-products-count">{{ count($products) }}</h5>
                        </div>
                    </div>
                </div>
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
                            <i class="fas fa-sort-amount-up fa-2x text-info mb-2"></i>
                            <h6 class="card-title">Total Opening Stock</h6>
                            <h5 class="text-info mb-0" id="total-opening-stock">0</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <h6 class="card-title">Changes Required</h6>
                            <h5 class="text-warning mb-0" id="changes-required-count">0</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <div class="d-flex gap-2">
                <button type="button" id="clear-all" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i> Clear All
                </button>
                <button type="button" id="set-current-stock" class="btn btn-outline-info">
                    <i class="fas fa-sync me-1"></i> Set Current Stock
                </button>
            </div>
            <button type="submit" class="btn btn-primary" id="submit-opening-balance">
                <i class="fas fa-save me-1"></i> Save Opening Balance
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all');
    const productCheckboxes = document.querySelectorAll('.product-checkbox');
    const openingQuantityInputs = document.querySelectorAll('.opening-quantity');
    const submitButton = document.getElementById('submit-opening-balance');

    // Statistics elements
    const selectedCountElement = document.getElementById('selected-products-count');
    const totalStockElement = document.getElementById('total-opening-stock');
    const changesRequiredElement = document.getElementById('changes-required-count');

    function updateStatistics() {
        let selectedCount = 0;
        let totalOpeningStock = 0;
        let changesRequired = 0;

        productCheckboxes.forEach((checkbox, index) => {
            const quantityInput = openingQuantityInputs[index];
            const currentStock = parseInt(quantityInput.dataset.currentStock) || 0;
            const openingQuantity = parseInt(quantityInput.value) || 0;

            if (checkbox.checked) {
                selectedCount++;
                totalOpeningStock += openingQuantity;
                if (openingQuantity !== currentStock) {
                    changesRequired++;
                }
            }

            // Enable/disable quantity input based on checkbox
            quantityInput.disabled = !checkbox.checked;

            // Update preview cell
            updatePreviewCell(quantityInput);
        });

        selectedCountElement.textContent = selectedCount;
        totalStockElement.textContent = totalOpeningStock;
        changesRequiredElement.textContent = changesRequired;

        // Enable/disable submit button
        submitButton.disabled = selectedCount === 0;
    }

    function updatePreviewCell(quantityInput) {
        const row = quantityInput.closest('tr');
        const previewCell = row.querySelector('.preview-container');
        const currentStock = parseInt(quantityInput.dataset.currentStock) || 0;
        const openingQuantity = parseInt(quantityInput.value) || 0;
        const productName = quantityInput.dataset.productName;

        if (openingQuantity === 0) {
            previewCell.innerHTML = '<small class="text-muted">Enter quantity</small>';
        } else {
            const difference = openingQuantity - currentStock;
            let differenceHtml = '';

            if (difference > 0) {
                differenceHtml = `<span class="text-success">+${difference}</span>`;
            } else if (difference < 0) {
                differenceHtml = `<span class="text-danger">${difference}</span>`;
            } else {
                differenceHtml = '<span class="text-muted">No change</span>';
            }

            previewCell.innerHTML = `
                <div>
                    <small class="text-muted">New: ${openingQuantity}</small><br>
                    <small>Change: ${differenceHtml}</small>
                </div>
            `;
        }
    }

    function setAllQuantities(currentOnly = false) {
        openingQuantityInputs.forEach((input, index) => {
            const currentStock = parseInt(input.dataset.currentStock) || 0;

            if (currentOnly) {
                input.value = currentStock;
            } else {
                input.value = 0;
            }

            // Check the product checkbox if quantity > 0
            const checkbox = productCheckboxes[index];
            checkbox.checked = currentOnly || parseInt(input.value) > 0;

            updatePreviewCell(input);
        });

        updateStatistics();
    }

    // Event listeners
    selectAllCheckbox.addEventListener('change', function() {
        productCheckboxes.forEach((checkbox, index) => {
            checkbox.checked = selectAllCheckbox.checked;
            openingQuantityInputs[index].disabled = !selectAllCheckbox.checked;

            if (selectAllCheckbox.checked) {
                updatePreviewCell(openingQuantityInputs[index]);
            }
        });
        updateStatistics();
    });

    productCheckboxes.forEach((checkbox, index) => {
        checkbox.addEventListener('change', updateStatistics);
    });

    openingQuantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Auto-check checkbox if quantity is entered
            const row = input.closest('tr');
            const checkbox = row.querySelector('.product-checkbox');
            if (parseInt(input.value) > 0) {
                checkbox.checked = true;
            }
            updateStatistics();
        });
    });

    document.getElementById('clear-all').addEventListener('click', function() {
        setAllQuantities(false);
    });

    document.getElementById('set-current-stock').addEventListener('click', function() {
        setAllQuantities(true);
        selectAllCheckbox.checked = true;
        updateStatistics();
    });

    // Initial statistics update
    updateStatistics();
});
</script>
@endpush
@endsection