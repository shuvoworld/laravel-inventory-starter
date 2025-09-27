@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Edit Purchase Order</h3>
    </div>
    <form method="POST" action="{{ route('modules.purchase-order.update', $item->id) }}" id="purchaseOrderForm">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="supplier_id" class="form-label">Supplier *</label>
                    <select id="supplier_id" name="supplier_id" class="form-control @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id', $item->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }} ({{ $supplier->code }})
                                @if($supplier->contact_person) - {{ $supplier->contact_person }}@endif
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="form-text text-muted">
                        Don't see your supplier? <a href="{{ route('modules.suppliers.create') }}" target="_blank">Add a new supplier</a>
                    </small>
                </div>
                <div class="col-md-6">
                    <label for="order_date" class="form-label">Order Date *</label>
                    <input id="order_date" type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', $item->order_date?->format('Y-m-d')) }}" required>
                    @error('order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="status" class="form-label">Status *</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ old('status', $item->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ old('status', $item->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ old('status', $item->status) == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="received" {{ old('status', $item->status) == 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ old('status', $item->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $item->notes) }}</textarea>
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
                @foreach($item->items as $index => $orderItem)
                <div class="row order-item mb-3" data-index="{{ $index }}">
                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $orderItem->id }}">
                    <div class="col-md-4">
                        <label class="form-label">Product *</label>
                        <select name="items[{{ $index }}][product_id]" class="form-control product-select" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" {{ $orderItem->product_id == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} (Current Stock: {{ $product->quantity_on_hand }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control quantity-input" min="1" value="{{ $orderItem->quantity }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit Price *</label>
                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control price-input" step="0.01" min="0" value="{{ $orderItem->unit_price }}" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total</label>
                        <input type="text" class="form-control total-display" value="${{ number_format($orderItem->total_price, 2) }}" readonly>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach

                @if($item->items->isEmpty())
                <div class="row order-item mb-3" data-index="0">
                    <div class="col-md-4">
                        <label class="form-label">Product *</label>
                        <select name="items[0][product_id]" class="form-control product-select" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                    {{ $product->name }} (Current Stock: {{ $product->quantity_on_hand }})
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
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @endif
            </div>

            <div class="row mt-4">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <strong>Order Total:</strong>
                                <strong id="orderTotal">${{ number_format($item->total_amount, 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Update Order
            </button>
            <a href="{{ route('modules.purchase-order.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<x-audits.table :model="$item" title="Last 10 Audits" />

@push('scripts')
<script>
let itemIndex = {{ $item->items->count() }};

document.addEventListener('DOMContentLoaded', function() {
    // Add item functionality
    document.getElementById('addItem').addEventListener('click', function() {
        const orderItems = document.getElementById('orderItems');
        const newItem = createNewItemRow(itemIndex);
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

    function createNewItemRow(index) {
        const div = document.createElement('div');
        div.className = 'row order-item mb-3';
        div.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Product *</label>
                <select name="items[${index}][product_id]" class="form-control product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                            {{ $product->name }} (Current Stock: {{ $product->quantity_on_hand }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Quantity *</label>
                <input type="number" name="items[${index}][quantity]" class="form-control quantity-input" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Unit Price *</label>
                <input type="number" name="items[${index}][unit_price]" class="form-control price-input" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Total</label>
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

    updateRemoveButtons();
    calculateTotal();
});
</script>
@endpush
@endsection
