@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Sales Order #{{ $item->order_number }}</h1>
    <div class="d-flex gap-2">
        @can('sales-return.create')
            <a href="{{ route('modules.sales-return.create', ['sales_order_id' => $item->id]) }}" class="btn btn-outline-warning">
                <i class="fas fa-undo me-1"></i> Create Return
            </a>
        @endcan
        @can('sales-order.view')
            <a href="{{ route('modules.sales-order.invoice', $item->id) }}" class="btn btn-outline-success" target="_blank">
                <i class="fas fa-print me-1"></i> Print Invoice
            </a>
        @endcan
        @can('sales-order.edit')
            <a href="{{ route('modules.sales-order.edit', $item->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        @endcan
        <a href="{{ route('modules.sales-order.index') }}" class="btn btn-secondary">Back to List</a>
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
                                <td><strong>Order Number:</strong></td>
                                <td>{{ $item->order_number }}</td>
                            </tr>
                            <tr>
                                <td><strong>Customer:</strong></td>
                                <td>{{ $item->customer ? $item->customer?->name : 'N/A' }}</td>
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
                                            'shipped' => 'badge-secondary',
                                            'delivered' => 'badge-success',
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
                                        <strong>{{ $orderItem->getDisplayName() }}</strong>
                                        @if($orderItem->variant)
                                            <br><small class="text-primary">
                                                {{ $orderItem->variant->optionValues->map(function($optionValue) {
                                                    return $optionValue->option->name . ': ' . $optionValue->value;
                                                })->implode(', ') }}
                                            </small>
                                        @endif
                                        @if($orderItem->product->unit)
                                            <br><small class="text-muted">Unit: {{ $orderItem->product->unit }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $orderItem->variant?->sku ?? $orderItem->product->sku ?? 'N/A' }}</td>
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
                      <tr class="border-top">
                        <td><strong>Total:</strong></td>
                        <td class="text-end"><strong>${{ number_format($item->total_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <td>Paid:</td>
                        <td class="text-end">${{ number_format($item->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Due:</td>
                        <td class="text-end text-danger">{{ '$' . number_format(max(0, $item->total_amount - $item->paid_amount), 2) }}</td>
                    </tr>
                    @if($item->change_amount > 0)
                    <tr>
                        <td>Change:</td>
                        <td class="text-end text-success">${{ number_format($item->change_amount, 2) }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ $item->customer?->name }}</td>
                    </tr>
                    @if($item->customer?->email)
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $item->customer?->email }}</td>
                        </tr>
                    @endif
                    @if($item->customer?->phone)
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $item->customer?->phone }}</td>
                        </tr>
                    @endif
                    @if($item->customer?->address)
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td>
                                {{ $item->customer?->address }}
                                @if($item->customer?->city || $item->customer->state)
                                    <br>{{ $item->customer?->city }}{{ $item->customer?->city && $item->customer?->state ? ', ' : '' }}{{ $item->customer?->state }}
                                @endif
                                @if($item->customer?->postal_code)
                                    <br>{{ $item->customer?->postal_code }}
                                @endif
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

@can('sales-order.edit')
    <!-- Hold Order Modal -->
    <div class="modal fade" id="holdOrderModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Order on Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('modules.sales-order.hold', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="hold_reason" class="form-label">Hold Reason *</label>
                            <textarea id="hold_reason" name="hold_reason" class="form-control" rows="3" required placeholder="Please provide a reason for placing this order on hold..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Place on Hold</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Payment Modal -->
    <div class="modal fade" id="updatePaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Payment Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('modules.sales-order.update-payment', $item->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method *</label>
                            <select id="payment_method" name="payment_method" class="form-control" required>
                                <option value="cash" {{ $item->payment_method === 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="card" {{ $item->payment_method === 'card' ? 'selected' : '' }}>Card</option>
                                <option value="mobile_banking" {{ $item->payment_method === 'mobile_banking' ? 'selected' : '' }}>Mobile Banking</option>
                                <option value="bank_transfer" {{ $item->payment_method === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ $item->payment_method === 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="paid_amount" class="form-label">Amount Paid *</label>
                            <input type="number" id="paid_amount" name="paid_amount" class="form-control" step="0.01" min="0" value="{{ $item->paid_amount }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" id="reference_number" name="reference_number" class="form-control" value="{{ $item->reference_number }}" placeholder="Check #, Transaction ID, etc.">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
@endsection
