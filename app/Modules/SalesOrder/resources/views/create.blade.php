@extends('layouts.module')

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

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Order Items</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" id="addItem">
                    <i class="fas fa-plus me-1"></i> Add Item
                </button>
            </div>

            <div id="orderItems">
                <div class="row order-item mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Product *</label>
                        <select name="items[0][product_id]" class="form-control product-select select2" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-stock="{{ $product->quantity_on_hand }}">
                                    {{ $product->name }} (Stock: {{ $product->quantity_on_hand }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Unit Price *</label>
                        <input type="number" name="items[0][unit_price]" class="form-control price-input" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Discount</label>
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
                        <label class="form-label">Total</label>
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
                                <div class="col-md-4">
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
                                <div class="col-md-4">
                                    <label for="discount_rate" class="form-label">Discount Rate</label>
                                    <input type="number" id="discount_rate" name="discount_rate" class="form-control" step="0.01" min="0" placeholder="0" value="{{ old('discount_rate') }}">
                                </div>
                                <div class="col-md-4">
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
    // Add item functionality
    document.getElementById('addItem').addEventListener('click', function() {
        const orderItems = document.getElementById('orderItems');
        const newItem = document.querySelector('.order-item').cloneNode(true);

        // Update names and reset values
        newItem.querySelectorAll('select, input').forEach(input => {
            const name = input.getAttribute('name');
            if (name) {
                input.setAttribute('name', name.replace('[0]', `[${itemIndex}]`));
            }
            if (input.type !== 'button') {
                input.value = '';
            }
        });

        // Re-initialize select2 for the new product dropdown
        const newSelect = newItem.querySelector('.product-select');
        if (newSelect && window.jQuery && jQuery.fn.select2) {
            jQuery(newSelect).select2({
                theme: 'bootstrap4',
                placeholder: 'Select Product',
                allowClear: false
            });
        }

        // Enable remove button
        newItem.querySelector('.remove-item').disabled = false;

        orderItems.appendChild(newItem);
        itemIndex++;
        updateRemoveButtons();
    });

    // Remove item functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.order-item').remove();
            updateRemoveButtons();
            calculateTotal();
        }
    });

    // Product selection and price calculation
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const option = e.target.selectedOptions[0];
            const row = e.target.closest('.order-item');
            const priceInput = row.querySelector('.price-input');

            if (option && option.dataset.price) {
                priceInput.value = option.dataset.price;
                calculateRowTotal(row);
            }
        }

        // Handle Select2 change events
        if (e.target.classList.contains('select2')) {
            setTimeout(() => {
                const select = e.target;
                const selectedOption = select.options[select.selectedIndex];
                const row = select.closest('.order-item');
                const priceInput = row.querySelector('.price-input');

                if (selectedOption && selectedOption.dataset.price) {
                    priceInput.value = selectedOption.dataset.price;
                    calculateRowTotal(row);
                }
            }, 100);
        }

        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input') ||
            e.target.classList.contains('discount-type') || e.target.classList.contains('discount-rate')) {
            calculateRowTotal(e.target.closest('.order-item'));
        }

        if (e.target.id === 'discount_type' || e.target.id === 'discount_rate' || e.target.id === 'paid_amount') {
            calculateOrderTotal();
        }
    });

    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input') ||
            e.target.classList.contains('discount-rate') || e.target.id === 'discount_rate' || e.target.id === 'paid_amount') {
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
        const orderDiscountRate = parseFloat(document.getElementById('discount_rate').value) || 0;
        let orderDiscount = 0;

        if (orderDiscountType === 'fixed') {
            orderDiscount = Math.min(orderDiscountRate, subtotal);
        } else if (orderDiscountType === 'percentage') {
            orderDiscount = subtotal * (orderDiscountRate / 100);
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

    function updateRemoveButtons() {
        const items = document.querySelectorAll('.order-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-item');
            removeBtn.disabled = items.length === 1;
        });
    }
});
</script>
@endpush
@endsection