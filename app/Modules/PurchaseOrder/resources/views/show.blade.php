@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Purchase Order #{{ $item->po_number }}</h1>
    <div class="d-flex gap-2">
        @can('purchase-return.create')
            <a href="{{ route('modules.purchase-return.create', ['purchase_order_id' => $item->id]) }}" class="btn btn-outline-warning">
                <i class="fas fa-undo me-1"></i> Create Return
            </a>
        @endcan
        @can('purchase-order.edit')
            <a href="{{ route('modules.purchase-order.edit', $item->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('modules.purchase-order.index') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>PO Number:</strong></td>
                                <td>{{ $item->po_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Supplier:</strong></td>
                                <td>{{ $item->supplier_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Order Date:</strong></td>
                                <td>{{ $item->order_date->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @php
                                        $badges = [
                                            'pending' => 'badge-warning',
                                            'confirmed' => 'badge-info',
                                            'processing' => 'badge-primary',
                                            'received' => 'badge-success',
                                            'cancelled' => 'badge-danger'
                                        ];
                                        $class = $badges[$item->status] ?? 'badge-secondary';
                                    @endphp
                                    <span class="badge {{ $class }}">{{ ucfirst($item->status) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Created:</strong></td>
                                <td>{{ $item->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Updated:</strong></td>
                                <td>{{ $item->updated_at->format('M d, Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($item->notes)
                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <h6>Notes:</h6>
                            <p class="text-muted">{{ $item->notes }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->items as $orderItem)
                                <tr>
                                    <td>
                                        <strong>{{ $orderItem->product->name }}</strong>
                                        @if($orderItem->product->unit)
                                            <br><small class="text-muted">Unit: {{ $orderItem->product->unit }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $orderItem->product->sku ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($orderItem->quantity) }}</td>
                                    <td class="text-end">${{ number_format($orderItem->unit_price, 2) }}</td>
                                    <td class="text-end">${{ number_format($orderItem->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td>Subtotal:</td>
                        <td class="text-end">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @if($item->discount_amount > 0)
                        <tr>
                            <td>Discount:</td>
                            <td class="text-end text-success">-${{ number_format($item->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($item->tax_amount > 0)
                        <tr>
                            <td>Tax:</td>
                            <td class="text-end">${{ number_format($item->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="border-top">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><strong>${{ number_format($item->total_amount, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment Status</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-3">
                    <tr>
                        <td>Total Amount:</td>
                        <td class="text-end"><strong>${{ number_format($item->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Paid Amount:</td>
                        <td class="text-end text-success"><strong>${{ number_format($item->paid_amount, 2) }}</strong></td>
                    </tr>
                    <tr class="border-top">
                        <td><strong>Due Amount:</strong></td>
                        <td class="text-end">
                            <strong class="{{ $item->due_amount > 0 ? 'text-danger' : 'text-success' }}">
                                ${{ number_format($item->due_amount, 2) }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td class="text-end">
                            @php
                                $paymentBadges = [
                                    'unpaid' => 'badge-danger',
                                    'partial' => 'badge-warning',
                                    'paid' => 'badge-success'
                                ];
                                $paymentClass = $paymentBadges[$item->payment_status] ?? 'badge-secondary';
                            @endphp
                            <span class="badge {{ $paymentClass }}">{{ ucfirst($item->payment_status) }}</span>
                        </td>
                    </tr>
                </table>

                @if(!$item->isPaid())
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="fas fa-plus me-1"></i> Add Payment
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

@if($item->payments->count() > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Payment History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Payment #</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_number }}</td>
                                <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                <td>${{ number_format($payment->amount, 2) }}</td>
                                <td>{{ $payment->payment_method ? ucfirst($payment->payment_method) : 'N/A' }}</td>
                                <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                                <td>{{ $payment->notes ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('modules.purchase-order.add-payment', $item->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Due Amount</label>
                        <input type="text" class="form-control" value="${{ number_format($item->due_amount, 2) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('payment_date') is-invalid @enderror"
                               id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        @error('payment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" max="{{ $item->due_amount }}"
                               class="form-control @error('amount') is-invalid @enderror"
                               id="amount" name="amount" value="{{ old('amount', $item->due_amount) }}" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method">
                            <option value="">Select Method</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="check" {{ old('payment_method') == 'check' ? 'selected' : '' }}>Check</option>
                            <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                               id="reference_number" name="reference_number" value="{{ old('reference_number') }}">
                        @error('reference_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection