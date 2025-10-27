@extends('layouts.module')

@push('styles')
<style>
/* Fix Select2 border and styling issues */
.select2-container--bootstrap4 .select2-selection {
    border: 1px solid #ced4da !important;
    border-radius: 0.375rem !important;
    background-color: #fff !important;
}

.select2-container--bootstrap4.select2-container--focus .select2-selection {
    border-color: #86b7fe !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.select2-container--bootstrap4 .select2-selection--single {
    height: 38px !important;
}

.select2-container--bootstrap4 .select2-selection__rendered {
    line-height: 36px !important;
    padding-left: 12px !important;
}

/* Make all inputs more compact */
.order-item .form-control,
.order-item .form-select {
    height: 38px !important;
    padding: 6px 12px !important;
    font-size: 0.875rem !important;
}

.order-item .input-group {
    display: flex;
    align-items: stretch;
}

.order-item .input-group .form-control {
    height: 38px !important;
}

.order-item .input-group .form-select {
    height: 38px !important;
}

/* Compact spacing for form labels */
.order-item .form-label {
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    margin-bottom: 0.25rem !important;
}

/* Compact discount input group */
.order-item .input-group .form-control.discount-rate {
    width: 70px !important;
    flex: none !important;
}

.order-item .input-group .form-select.discount-type {
    flex: 1 !important;
    min-width: 80px !important;
}
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Create Sales Order</h3>
    </div>
    <form method="POST" action="{{ route('modules.sales-order.store') }}" id="salesOrderForm">
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="customer_id" class="form-label">Customer *</label>
                    <select id="customer_id" name="customer_id" class="form-control @error('customer_id') is-invalid @enderror" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="order_date" class="form-label">Order Date *</label>
                    <input id="order_date" type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', date('Y-m-d')) }}" required>
                    @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <hr class="my-4">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Order Items</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </div>

            <div id="orderItems">
                <div class="row order-item mb-2">
                    <div class="col-md-3">
                        <label class="form-label mb-1">Product *</label>
                        <select name="items[0][product_id]" class="form-control product-select select2" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-price="{{ $product->target_price ?? $product->price }}"
                                        data-floor-price="{{ $product->floor_price }}"
                                        data-target-price="{{ $product->target_price }}"
                                        data-stock="{{ $product->quantity_on_hand }}"
                                        data-has-variants="{{ $product->has_variants ? 'true' : 'false' }}">
                                    {{ $product->name }} (Stock: {{ $product->quantity_on_hand }}) {{ $product->has_variants ? '[V]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 variant-selection-container" style="display: none;">
                        <label class="form-label mb-1">Variant *</label>
                        <select name="items[0][variant_id]" class="form-control variant-select">
                            <option value="">Select Variant</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label mb-1">Qty *</label>
                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1">Price *</label>
                        <input type="number" name="items[0][unit_price]" class="form-control price-input" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label mb-1">Discount</label>
                        <div class="input-group">
                            <select name="items[0][discount_type]" class="form-control discount-type">
                                <option value="none">None</option>
                                <option value="fixed">$</option>
                                <option value="percentage">%</option>
                            </select>
                            <input type="number" name="items[0][discount_rate]" class="form-control discount-rate" step="0.01" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label mb-1">Total</label>
                        <input type="text" class="form-control total-display" readonly>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item" disabled>
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-8">
                    <!-- Order Level Discount -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">Order Level Discount</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="discount_type" class="form-label">Discount Type</label>
                                    <select id="discount_type" name="discount_type" class="form-control">
                                        <option value="">No Discount</option>
                                        @foreach($discountTypes as $key => $label)
                                            <option value="{{ $key }}" {{ old('discount_type') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3" id="fixed_amount_field" style="display: none;">
                                    <label for="discount_amount" class="form-label">Discount Amount ($)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="discount_amount" name="discount_amount" class="form-control" step="0.01" min="0" placeholder="0.00" value="{{ old('discount_amount') }}">
                                    </div>
                                </div>
                                <div class="col-md-3" id="percentage_field" style="display: none;">
                                    <label for="discount_rate" class="form-label">Discount Rate (%)</label>
                                    <div class="input-group">
                                        <input type="number" id="discount_rate" name="discount_rate" class="form-control" step="0.01" min="0" max="100" placeholder="0" value="{{ old('discount_rate') }}">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="discount_reason" class="form-label">Discount Reason</label>
                                    <input type="text" id="discount_reason" name="discount_reason" class="form-control" placeholder="Optional" value="{{ old('discount_reason') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Payment Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="payment_method" class="form-label">Payment Method *</label>
                                    <select id="payment_method" name="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
                                        <option value="">Select Payment Method</option>
                                        @foreach($paymentMethods as $key => $label)
                                            <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="paid_amount" class="form-label">Amount Paid *</label>
                                    <input type="number" id="paid_amount" name="paid_amount" class="form-control" step="0.01" min="0" required value="{{ old('paid_amount', 0) }}">
                                    @error('paid_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <label for="reference_number" class="form-label">Reference Number</label>
                                    <input type="text" id="reference_number" name="reference_number" class="form-control" placeholder="Check #, Transaction ID, etc." value="{{ old('reference_number') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <span>Subtotal:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Discount:</span>
                                <span id="discountAmount">-$0.00</span>
                            </div>
                              <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong id="orderTotal">$0.00</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Paid:</span>
                                <span id="paidDisplay">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Due:</span>
                                <span id="dueDisplay" class="text-danger">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between" id="changeRow" style="display: none;">
                                <span>Change:</span>
                                <span id="changeDisplay" class="text-success">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Create Order
            </button>
            <a href="{{ route('modules.sales-order.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
let itemIndex = 1;

document.addEventListener('DOMContentLoaded', function() {
    // Debug: Log all product options on page load
    console.log('Sales order form loaded');
    const productSelect = document.querySelector('.product-select');
    if (productSelect) {
        console.log('Product select found');
        const options = productSelect.querySelectorAll('option');
        console.log('Total product options:', options.length);

        options.forEach((option, index) => {
            if (option.value) {
                console.log(`Option ${index}:`, {
                    value: option.value,
                    text: option.text,
                    hasVariants: option.dataset.hasVariants,
                    allData: Object.keys(option.dataset).map(key => `${key}: ${option.dataset[key]}`)
                });
            }
        });
    }

    // Add item functionality
    document.getElementById('addItem').addEventListener('click', function() {
        const orderItems = document.getElementById('orderItems');
        const newItem = createNewItemRow(itemIndex);
        orderItems.appendChild(newItem);

        // Initialize Select2 for the new row with proper styling
        const newSelect = newItem.querySelector('.product-select');
        if (newSelect && window.jQuery && jQuery.fn.select2) {
            jQuery(newSelect).select2({
                theme: 'bootstrap4',
                placeholder: 'Select Product',
                allowClear: false,
                width: '100%'
            });
        }

        itemIndex++;
        updateRemoveButtons();
    });

    // Create new item row HTML
    function createNewItemRow(index) {
        const div = document.createElement('div');
        div.className = 'row order-item mb-2';
        div.innerHTML = `
            <div class="col-md-3">
                <label class="form-label mb-1">Product *</label>
                <select name="items[${index}][product_id]" class="form-control product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                                data-price="{{ $product->target_price ?? $product->price }}"
                                data-floor-price="{{ $product->floor_price }}"
                                data-target-price="{{ $product->target_price }}"
                                data-stock="{{ $product->quantity_on_hand }}"
                                data-has-variants="{{ $product->has_variants ? 'true' : 'false' }}">
                            {{ $product->name }} (Stock: {{ $product->quantity_on_hand }}) {{ $product->has_variants ? '[V]' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 variant-selection-container" style="display: none;">
                <label class="form-label mb-1">Variant *</label>
                <select name="items[${index}][variant_id]" class="form-control variant-select">
                    <option value="">Select Variant</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1">Qty *</label>
                <input type="number" name="items[${index}][quantity]" class="form-control quantity-input" min="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Price *</label>
                <input type="number" name="items[${index}][unit_price]" class="form-control price-input" step="0.01" min="0" required>
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1">Discount</label>
                <div class="input-group">
                    <select name="items[${index}][discount_type]" class="form-control discount-type">
                        <option value="none">None</option>
                        <option value="fixed">$</option>
                        <option value="percentage">%</option>
                    </select>
                    <input type="number" name="items[${index}][discount_rate]" class="form-control discount-rate" step="0.01" min="0" placeholder="0">
                </div>
            </div>
            <div class="col-md-1">
                <label class="form-label mb-1">Total</label>
                <input type="text" class="form-control total-display" readonly>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        return div;
    }

    // Remove item functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.order-item').remove();
            updateRemoveButtons();
            calculateTotal();
        }
    });

    // Product selection and variant loading
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const option = e.target.selectedOptions[0];
            const row = e.target.closest('.order-item');
            const priceInput = row.querySelector('.price-input');
            const variantContainer = row.querySelector('.variant-selection-container');

            if (option && option.value) {
                const hasVariants = option.dataset.hasVariants === 'true';
                const productId = option.value;

                console.log('Product selected:', {
                    productId: productId,
                    productName: option.text,
                    hasVariants: hasVariants,
                    dataset: option.dataset
                });

                if (hasVariants) {
                    // Show variant selection and load variants
                    console.log('Showing variant selection container');
                    variantContainer.style.display = 'block';
                    loadProductVariants(productId, row);
                } else {
                    // Hide variant selection
                    console.log('Hiding variant selection container');
                    variantContainer.style.display = 'none';
                    const variantSelect = row.querySelector('.variant-select');
                    variantSelect.innerHTML = '<option value="">Select Variant</option>';
                    variantSelect.value = '';
                }

                if (option.dataset.price) {
                    priceInput.value = option.dataset.price;
                    calculateRowTotal(row);
                }
            } else {
                // Reset when no product is selected
                variantContainer.style.display = 'none';
                priceInput.value = '';
                calculateRowTotal(row);
            }
        }

        // Handle variant selection
        if (e.target.classList.contains('variant-select')) {
            const row = e.target.closest('.order-item');
            const option = e.target.selectedOptions[0];
            const priceInput = row.querySelector('.price-input');

            if (option && option.dataset.price) {
                priceInput.value = option.dataset.price;
                calculateRowTotal(row);
            }
        }

        // Handle Select2 change events
        if (e.target.classList.contains('select2')) {
            console.log('Select2 change detected on:', e.target);
            setTimeout(() => {
                const select = e.target;
                const selectedOption = select.options[select.selectedIndex];
                const row = select.closest('.order-item');
                const priceInput = row.querySelector('.price-input');
                const variantContainer = row.querySelector('.variant-selection-container');

                console.log('Select2 processing:', {
                    selectedIndex: select.selectedIndex,
                    selectedOption: selectedOption ? selectedOption.textContent : 'none',
                    hasVariants: selectedOption ? selectedOption.dataset.hasVariants : 'no option'
                });

                if (selectedOption && selectedOption.value) {
                    const hasVariants = selectedOption.dataset.hasVariants === 'true';
                    const productId = selectedOption.value;

                    if (hasVariants) {
                        // Show variant selection and load variants
                        console.log('Select2: Showing variant selection');
                        variantContainer.style.display = 'block';
                        loadProductVariants(productId, row);
                    } else {
                        // Hide variant selection
                        console.log('Select2: Hiding variant selection');
                        variantContainer.style.display = 'none';
                        const variantSelect = row.querySelector('.variant-select');
                        variantSelect.innerHTML = '<option value="">Select Variant</option>';
                        variantSelect.value = '';
                    }

                    if (selectedOption.dataset.price) {
                        priceInput.value = selectedOption.dataset.price;
                        calculateRowTotal(row);
                    }
                } else {
                    // Reset when no product is selected
                    console.log('Select2: Resetting - no selection');
                    variantContainer.style.display = 'none';
                    priceInput.value = '';
                    calculateRowTotal(row);
                }
            }, 100);
        }

        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input') ||
            e.target.classList.contains('discount-type') || e.target.classList.contains('discount-rate') ||
            e.target.classList.contains('variant-select')) {

            // Validate floor price when price is changed
            if (e.target.classList.contains('price-input')) {
                validateFloorPrice(e.target);
            }

            calculateRowTotal(e.target.closest('.order-item'));
        }

        if (e.target.id === 'discount_type' || e.target.id === 'discount_amount' || e.target.id === 'discount_rate' || e.target.id === 'paid_amount') {
            calculateOrderTotal();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input') ||
            e.target.classList.contains('discount-rate') || e.target.id === 'discount_amount' || e.target.id === 'discount_rate' || e.target.id === 'paid_amount') {

            // Validate floor price when price is changed
            if (e.target.classList.contains('price-input')) {
                validateFloorPrice(e.target);
            }

            calculateOrderTotal();
        }
    });

    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discountType = row.querySelector('.discount-type').value;
        const discountRate = parseFloat(row.querySelector('.discount-rate').value) || 0;

        let rowTotal = quantity * price;
        let discount = 0;

        if (discountType === 'fixed') {
            discount = Math.min(discountRate, rowTotal);
        } else if (discountType === 'percentage') {
            discount = rowTotal * (discountRate / 100);
        }

        const finalTotal = rowTotal - discount;
        row.querySelector('.total-display').value = '$' + finalTotal.toFixed(2);
        calculateOrderTotal();
    }

    function calculateOrderTotal() {
        let subtotal = 0;
        document.querySelectorAll('.order-item').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            subtotal += quantity * price;
        });

        // Calculate order level discount
        const orderDiscountType = document.getElementById('discount_type').value;
        const fixedAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const percentageRate = parseFloat(document.getElementById('discount_rate').value) || 0;
        let orderDiscount = 0;

        if (orderDiscountType === 'fixed') {
            orderDiscount = Math.min(fixedAmount, subtotal);
        } else if (orderDiscountType === 'percentage') {
            orderDiscount = subtotal * (percentageRate / 100);
        }

        // Calculate final total (no tax)
        const total = subtotal - orderDiscount;

        // Calculate due and change
        const paidAmount = parseFloat(document.getElementById('paid_amount').value) || 0;
        const due = Math.max(0, total - paidAmount);
        const change = Math.max(0, paidAmount - total);

        // Update display
        document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
        document.getElementById('discountAmount').textContent = '-$' + orderDiscount.toFixed(2);
        document.getElementById('orderTotal').textContent = '$' + total.toFixed(2);
        document.getElementById('paidDisplay').textContent = '$' + paidAmount.toFixed(2);
        document.getElementById('dueDisplay').textContent = '$' + due.toFixed(2);

        // Show/hide change based on payment
        const changeRow = document.getElementById('changeRow');
        if (paidAmount > 0) {
            changeRow.style.display = 'flex';
            document.getElementById('changeDisplay').textContent = '$' + change.toFixed(2);
        } else {
            changeRow.style.display = 'none';
        }
    }

    function validateFloorPrice(priceInput) {
        const row = priceInput.closest('.order-item');
        const productSelect = row.querySelector('.product-select');
        const selectedOption = productSelect.selectedOptions[0];
        const price = parseFloat(priceInput.value) || 0;
        const floorPrice = parseFloat(selectedOption.dataset.floorPrice) || 0;

        if (floorPrice > 0 && price < floorPrice) {
            priceInput.setCustomValidity(`Price cannot be below floor price ($${floorPrice.toFixed(2)})`);
            priceInput.classList.add('is-invalid');

            // Show error message
            let errorDiv = row.querySelector('.floor-price-error');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.className = 'floor-price-error text-danger small mt-1';
                priceInput.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = `Minimum price: $${floorPrice.toFixed(2)}`;
        } else {
            priceInput.setCustomValidity('');
            priceInput.classList.remove('is-invalid');

            // Remove error message
            const errorDiv = row.querySelector('.floor-price-error');
            if (errorDiv) {
                errorDiv.remove();
            }
        }
    }

    function updateRemoveButtons() {
        const items = document.querySelectorAll('.order-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-item');
            removeBtn.disabled = items.length === 1;
        });
    }

    // Load product variants via AJAX
    async function loadProductVariants(productId, row) {
        console.log('Loading variants for product:', productId);
        try {
            const response = await fetch(`/api/products/${productId}/variants`);
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Variants data received:', data);

            const variantSelect = row.querySelector('.variant-select');
            variantSelect.innerHTML = '<option value="">Select Variant</option>';

            if (data.success && data.data && data.data.length > 0) {
                console.log('Found variants:', data.data.length);
                data.data.forEach(variant => {
                    const option = document.createElement('option');
                    option.value = variant.id;
                    option.dataset.price = variant.target_price || variant.price || 0;
                    option.dataset.stock = variant.quantity_on_hand || 0;
                    option.textContent = `${variant.variant_name} (Stock: ${variant.quantity_on_hand || 0})`;
                    variantSelect.appendChild(option);
                });
            } else {
                console.log('No variants found or invalid data');
                const option = document.createElement('option');
                option.value = "";
                option.textContent = "No variants available";
                option.disabled = true;
                variantSelect.appendChild(option);
            }
        } catch (error) {
            console.error('Error loading variants:', error);
            const variantSelect = row.querySelector('.variant-select');
            variantSelect.innerHTML = '<option value="">Error loading variants</option>';
        }
    }

    // Handle order level discount type change
    document.addEventListener('change', function(e) {
        if (e.target.id === 'discount_type') {
            handleOrderDiscountTypeChange(e.target.value);
        }
    });

    // Function to handle order discount type change
    function handleOrderDiscountTypeChange(discountType) {
        const fixedField = document.getElementById('fixed_amount_field');
        const percentageField = document.getElementById('percentage_field');
        const fixedInput = document.getElementById('discount_amount');
        const percentageInput = document.getElementById('discount_rate');

        // Hide both fields first
        fixedField.style.display = 'none';
        percentageField.style.display = 'none';

        // Clear both inputs
        fixedInput.value = '';
        percentageInput.value = '';

        // Show appropriate field based on discount type
        if (discountType === 'fixed') {
            fixedField.style.display = 'block';
        } else if (discountType === 'percentage') {
            percentageField.style.display = 'block';
        }

        // Recalculate order total
        calculateOrderTotal();
    }

    // Initialize discount type on page load
    document.addEventListener('DOMContentLoaded', function() {
        const discountTypeSelect = document.getElementById('discount_type');
        if (discountTypeSelect && discountTypeSelect.value) {
            handleOrderDiscountTypeChange(discountTypeSelect.value);
        }
    });

    // Initialize Select2 and add variant-specific event handlers
    if (window.jQuery && jQuery.fn.select2) {
        console.log('Initializing Select2 with variant support');

        // Initialize all existing Select2 dropdowns
        $('.product-select').select2({
            theme: 'bootstrap4',
            placeholder: 'Select Product',
            allowClear: false,
            width: '100%'
        });

        $(document).on('select2:select', '.product-select', function(e) {
            console.log('Select2:select event triggered');
            const data = e.params.data;
            const option = e.target.querySelector(`option[value="${data.id}"]`);
            const row = $(this).closest('.order-item')[0];
            const variantContainer = row.querySelector('.variant-selection-container');

            console.log('Select2 select event data:', {
                selectedId: data.id,
                selectedText: data.text,
                hasVariants: option ? option.dataset.hasVariants : 'not found'
            });

            if (option && option.dataset.hasVariants === 'true') {
                console.log('Select2: Showing variants for product', data.id);
                variantContainer.style.display = 'block';
                loadProductVariants(data.id, row);
            } else {
                console.log('Select2: Hiding variants');
                variantContainer.style.display = 'none';
                const variantSelect = row.querySelector('.variant-select');
                variantSelect.innerHTML = '<option value="">Select Variant</option>';
                variantSelect.value = '';
            }
        });

        $(document).on('select2:unselect', '.product-select', function(e) {
            console.log('Select2:unselect event triggered');
            const row = $(this).closest('.order-item')[0];
            const variantContainer = row.querySelector('.variant-selection-container');
            variantContainer.style.display = 'none';
        });
    }
});
</script>
@endpush
@endsection