@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title mb-0">Create Purchase Return</h3>
    </div>
    <form method="POST" action="{{ route('modules.purchase-return.store') }}" id="purchaseReturnForm">
        @csrf
        <div class="card-body">
            @if($purchaseOrder)
                <input type="hidden" name="purchase_order_id" value="{{ $purchaseOrder->id }}">

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Purchase Order</label>
                        <input type="text" class="form-control" value="{{ $purchaseOrder->po_number }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supplier</label>
                        <input type="text" class="form-control" value="{{ $purchaseOrder->supplier_name }}" readonly>
                    </div>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="return_date" class="form-label">Return Date *</label>
                    <input id="return_date" type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" value="{{ old('return_date', date('Y-m-d')) }}" required>
                    @error('return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="reason" class="form-label">Reason *</label>
                    <select id="reason" name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                        <option value="">Select Reason</option>
                        <option value="defective" {{ old('reason') == 'defective' ? 'selected' : '' }}>Defective</option>
                        <option value="damaged" {{ old('reason') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="wrong_item" {{ old('reason') == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                        <option value="quality_issue" {{ old('reason') == 'quality_issue' ? 'selected' : '' }}>Quality Issue</option>
                        <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            @if($purchaseOrder && $purchaseOrder->items->count() > 0)
                <hr class="my-4">

                <h5 class="mb-3">Items to Return</h5>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Original Qty</th>
                                <th>Unit Price</th>
                                <th>Return Qty</th>
                                <th>Return Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->items as $index => $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->product->sku)
                                            <br><small class="text-muted">SKU: {{ $item->product->sku }}</small>
                                        @endif
                                    </td>
                                    <td>{{ number_format($item->quantity) }}</td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>
                                        <input type="hidden" name="items[{{ $index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                        <input type="number" name="items[{{ $index }}][quantity_returned]"
                                               class="form-control quantity-input"
                                               min="0" max="{{ $item->quantity }}"
                                               value="0"
                                               data-price="{{ $item->unit_price }}"
                                               style="width: 100px;">
                                    </td>
                                    <td class="return-total">$0.00</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <strong>Return Total:</strong>
                                    <strong id="returnTotal">$0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div class="card-footer d-flex gap-2">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-undo me-1"></i> Create Return
            </button>
            <a href="{{ route('modules.purchase-order.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate return totals
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity-input')) {
            calculateReturnTotal();
        }
    });

    function calculateReturnTotal() {
        let total = 0;
        document.querySelectorAll('.quantity-input').forEach(input => {
            const quantity = parseFloat(input.value) || 0;
            const price = parseFloat(input.dataset.price) || 0;
            const rowTotal = quantity * price;

            // Update row total
            const row = input.closest('tr');
            const totalCell = row.querySelector('.return-total');
            totalCell.textContent = '$' + rowTotal.toFixed(2);

            total += rowTotal;
        });

        document.getElementById('returnTotal').textContent = '$' + total.toFixed(2);
    }

    // Validate form submission
    document.getElementById('purchaseReturnForm').addEventListener('submit', function(e) {
        let hasItems = false;
        document.querySelectorAll('.quantity-input').forEach(input => {
            if (parseFloat(input.value) > 0) {
                hasItems = true;
            }
        });

        if (!hasItems) {
            e.preventDefault();
            alert('Please select at least one item to return.');
        }
    });
});
</script>
@endpush
@endsection