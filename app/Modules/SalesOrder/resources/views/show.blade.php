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
                                <td>{{ $item->customer->name }}</td>
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

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Name:</strong></td>
                        <td>{{ $item->customer->name }}</td>
                    </tr>
                    @if($item->customer->email)
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $item->customer->email }}</td>
                        </tr>
                    @endif
                    @if($item->customer->phone)
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>{{ $item->customer->phone }}</td>
                        </tr>
                    @endif
                    @if($item->customer->address)
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td>
                                {{ $item->customer->address }}
                                @if($item->customer->city || $item->customer->state)
                                    <br>{{ $item->customer->city }}{{ $item->customer->city && $item->customer->state ? ', ' : '' }}{{ $item->customer->state }}
                                @endif
                                @if($item->customer->postal_code)
                                    <br>{{ $item->customer->postal_code }}
                                @endif
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection