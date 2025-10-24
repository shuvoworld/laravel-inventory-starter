@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">
            @if(request('type') === 'opening_balance')
                <i class="fas fa-balance-scale me-2"></i>Set Opening Balance
            @else
                <i class="fas fa-plus me-2"></i>Create Stock Adjustment
            @endif
        </h3>
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
                    <label for="quantity" class="form-label">Quantity *</label>
                    <input id="quantity" type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity') }}" min="1" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="transaction_type" class="form-label">Transaction Type *</label>
                    <select id="transaction_type" name="transaction_type" class="form-control @error('transaction_type') is-invalid @enderror" required>
                        <option value="">Select Transaction Type</option>

                        <optgroup label="📦 STOCK IN (Increase Stock)">
                            <option value="opening_stock" {{ old('transaction_type') == 'opening_stock' || request('type') === 'opening_balance' ? 'selected' : '' }}>🏪 Opening Balance</option>
                            <option value="purchase" {{ old('transaction_type') == 'purchase' ? 'selected' : '' }}>📦 Purchase Order</option>
                            <option value="sale_return" {{ old('transaction_type') == 'sale_return' ? 'selected' : '' }}>↩️ Sales Return</option>
                            <option value="purchase_return" {{ old('transaction_type') == 'purchase_return' ? 'selected' : '' }}>🔄 Purchase Return</option>
                            <option value="transfer_in" {{ old('transaction_type') == 'transfer_in' ? 'selected' : '' }}>📥 Transfer IN</option>
                            <option value="stock_count_correction" {{ old('transaction_type') == 'stock_count_correction' ? 'selected' : '' }}>✏️ Stock Count (+)</option>
                            <option value="recovery_found" {{ old('transaction_type') == 'recovery_found' ? 'selected' : '' }}>🔍 Found/Recovered</option>
                            <option value="manufacturing_in" {{ old('transaction_type') == 'manufacturing_in' ? 'selected' : '' }}>🏭 Manufacturing IN</option>
                        </optgroup>

                        <optgroup label="📤 STOCK OUT (Decrease Stock)">
                            <option value="sale" {{ old('transaction_type') == 'sale' ? 'selected' : '' }}>💰 Sales Order</option>
                            <option value="damage" {{ old('transaction_type') == 'damage' ? 'selected' : '' }}>⚠️ Damage</option>
                            <option value="lost_missing" {{ old('transaction_type') == 'lost_missing' ? 'selected' : '' }}>❌ Lost/Missing</option>
                            <option value="theft" {{ old('transaction_type') == 'theft' ? 'selected' : '' }}>🔒 Theft</option>
                            <option value="expired" {{ old('transaction_type') == 'expired' ? 'selected' : '' }}>⏰ Expired</option>
                            <option value="transfer_out" {{ old('transaction_type') == 'transfer_out' ? 'selected' : '' }}>📤 Transfer OUT</option>
                            <option value="stock_count_correction_minus" {{ old('transaction_type') == 'stock_count_correction_minus' ? 'selected' : '' }}>✏️ Stock Count (-)</option>
                            <option value="quality_control" {{ old('transaction_type') == 'quality_control' ? 'selected' : '' }}>🚫 Quality Control</option>
                            <option value="manufacturing_out" {{ old('transaction_type') == 'manufacturing_out' ? 'selected' : '' }}>🏭 Manufacturing OUT</option>
                            <option value="promotional" {{ old('transaction_type') == 'promotional' ? 'selected' : '' }}>🎁 Promotional/Sample</option>
                        </optgroup>

                        <optgroup label="🔧 STOCK ADJUSTMENTS">
                            <option value="stock_correction" {{ old('transaction_type') == 'stock_correction' ? 'selected' : '' }}>🔧 Manual Stock Correction</option>
                            <option value="manual_adjustment" {{ old('transaction_type') == 'manual_adjustment' ? 'selected' : '' }}>✋ Manual Adjustment</option>
                        </optgroup>
                    </select>
                    @error('transaction_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                <i class="fas fa-save me-1"></i>
                @if(request('type') === 'opening_balance')
                    Set Opening Balance
                @else
                    Create Adjustment
                @endif
            </button>
            <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const transactionTypeSelect = document.getElementById('transaction_type');
    const quantityInput = document.getElementById('quantity');
    const stockPreview = document.getElementById('stock-preview');
    const noPreview = document.getElementById('no-preview');
    const currentStockSpan = document.getElementById('current-stock');
    const adjustmentDisplay = document.getElementById('adjustment-display');
    const newStockSpan = document.getElementById('new-stock');

    // Stock IN transaction types
    const stockInTypes = ['opening_stock', 'stock_count_correction', 'recovery_found', 'sale_return', 'transfer_in'];

    // Show opening balance help text
    function showOpeningBalanceHelp() {
        const selectedType = transactionTypeSelect.value;
        const helpText = document.getElementById('opening-balance-help');

        if (selectedType === 'opening_stock') {
            if (!helpText) {
                const helpDiv = document.createElement('div');
                helpDiv.id = 'opening-balance-help';
                helpDiv.className = 'alert alert-info mt-3';
                helpDiv.innerHTML = `
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Opening Balance:</strong> Use this to set initial stock quantities when first setting up your inventory or when reconciling physical counts at the beginning of a period.
                `;
                transactionTypeSelect.parentNode.appendChild(helpDiv);
            }
        } else {
            if (helpText) {
                helpText.remove();
            }
        }
    }

    function updatePreview() {
        const selectedOption = productSelect.selectedOptions[0];
        const transactionType = transactionTypeSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;

        if (!selectedOption || !selectedOption.dataset.stock || !transactionType || !quantity) {
            stockPreview.classList.add('d-none');
            noPreview.classList.remove('d-none');
            return;
        }

        const currentStock = parseInt(selectedOption.dataset.stock);
        let adjustment = 0;
        let newStock = currentStock;

        // Determine if this is a stock IN or OUT movement
        if (stockInTypes.includes(transactionType)) {
            adjustment = quantity;
            newStock = currentStock + quantity;
        } else {
            adjustment = -quantity;
            newStock = Math.max(0, currentStock - quantity);
        }

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

        // Show adjustment with appropriate color
        if (adjustment > 0) {
            adjustmentDisplay.innerHTML = '<span class="text-success">+' + adjustment + '</span>';
        } else if (adjustment < 0) {
            adjustmentDisplay.innerHTML = '<span class="text-danger">' + adjustment + '</span>';
        } else {
            adjustmentDisplay.innerHTML = '<span class="text-muted">0</span>';
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
    transactionTypeSelect.addEventListener('change', function() {
        showOpeningBalanceHelp();
        updatePreview();
    });
    quantityInput.addEventListener('input', updatePreview);
});
</script>
@endpush
@endsection
