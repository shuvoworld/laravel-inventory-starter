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
                <div class="col-12">
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
                        <select name="items[0][product_id]" class="form-control product-select" required>
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
                    <div class="col-md-3">
                        <label class="form-label">Unit Price *</label>
                        <input type="number" name="items[0][unit_price]" class="form-control price-input" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
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
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <strong>Order Total:</strong>
                                <strong id="orderTotal">$0.00</strong>
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

            if (option.dataset.price) {
                priceInput.value = option.dataset.price;
                calculateRowTotal(row);
            }
        }

        if (e.target.classList.contains('quantity-input') || e.target.classList.contains('price-input')) {
            calculateRowTotal(e.target.closest('.order-item'));
        }
    });

    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = quantity * price;

        row.querySelector('.total-display').value = '$' + total.toFixed(2);
        calculateTotal();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.order-item').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            total += quantity * price;
        });

        document.getElementById('orderTotal').textContent = '$' + total.toFixed(2);
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