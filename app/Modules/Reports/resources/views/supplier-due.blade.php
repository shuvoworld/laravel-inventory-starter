@extends('layouts.app')

@section('title', 'Supplier Due Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Supplier Due Report</h1>
                <button class="btn btn-primary" onclick="window.print()">Print Report</button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Filters</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('modules.reports.supplier-due') }}" class="row g-3">
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>All</option>
                        <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="overdue" {{ $filters['status'] == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="paid" {{ $filters['status'] == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($allSuppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $filters['supplier_id'] == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="start_date" class="form-label">Order Date From</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $filters['start_date'] }}">
                </div>
                <div class="col-md-2">
                    <label for="end_date" class="form-label">Order Date To</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $filters['end_date'] }}">
                </div>
                <div class="col-md-2">
                    <label for="due_date_from" class="form-label">Due Date From</label>
                    <input type="date" name="due_date_from" id="due_date_from" class="form-control" value="{{ $filters['due_date_from'] }}">
                </div>
                <div class="col-md-2">
                    <label for="due_date_to" class="form-label">Due Date To</label>
                    <input type="date" name="due_date_to" id="due_date_to" class="form-control" value="{{ $filters['due_date_to'] }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('modules.reports.supplier-due') }}" class="btn btn-outline-secondary">Clear Filters</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Due</h5>
                    <h2 class="card-text">${{ number_format($summary['total_due'], 2) }}</h2>
                    <small class="text-white-50">{{ $summary['supplier_count'] }} Suppliers</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Overdue Amount</h5>
                    <h2 class="card-text">${{ number_format($summary['total_overdue'], 2) }}</h2>
                    <small class="text-white-50">{{ $summary['overdue_count'] }} Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Pending Amount</h5>
                    <h2 class="card-text">${{ number_format($summary['total_pending'], 2) }}</h2>
                    <small class="text-white-50">{{ $summary['pending_count'] }} Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <h2 class="card-text">{{ $purchaseOrders->count() }}</h2>
                    <small class="text-white-50">All Statuses</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Supplier Breakdown -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Outstanding Amounts by Supplier</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Total Due</th>
                            <th>Overdue</th>
                            <th>Pending</th>
                            <th>Order Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplierData)
                            <tr>
                                <td>{{ $supplierData['supplier']->name }}</td>
                                <td><strong>${{ number_format($supplierData['total_due'], 2) }}</strong></td>
                                <td class="text-danger">${{ number_format($supplierData['overdue'], 2) }}</td>
                                <td class="text-warning">${{ number_format($supplierData['pending'], 2) }}</td>
                                <td>{{ $supplierData['order_count'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Overdue Orders -->
    @if($overdueOrders->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h6 class="mb-0">Overdue Orders ({{ $overdueOrders->count() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Payment Status</th>
                            <th>Days Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueOrders as $order)
                            <tr>
                                <td><strong>{{ $order->po_number }}</strong></td>
                                <td>{{ $order->supplier->name }}</td>
                                <td>{{ $order->order_date->format('M d, Y') }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>${{ number_format($order->paid_amount, 2) }}</td>
                                <td class="text-danger"><strong>${{ number_format($order->total_amount - $order->paid_amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>{{ $order->order_date->diffInDays(now()) }} days</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Pending Orders -->
    @if($pendingOrders->count() > 0)
    <div class="card mb-4">
        <div class="card-header bg-warning text-white">
            <h6 class="mb-0">Pending Orders ({{ $pendingOrders->count() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Payment Status</th>
                            <th>Days Since Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingOrders as $order)
                            <tr>
                                <td><strong>{{ $order->po_number }}</strong></td>
                                <td>{{ $order->supplier->name }}</td>
                                <td>{{ $order->order_date->format('M d, Y') }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>${{ number_format($order->paid_amount, 2) }}</td>
                                <td class="text-warning"><strong>${{ number_format($order->total_amount - $order->paid_amount, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>{{ $order->order_date->diffInDays(now()) }} days</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- All Orders Details -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">All Purchase Orders ({{ $purchaseOrders->count() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Due Amount</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchaseOrders as $order)
                            <tr>
                                <td><strong>{{ $order->po_number }}</strong></td>
                                <td>{{ $order->supplier->name }}</td>
                                <td>{{ $order->order_date->format('M d, Y') }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td>${{ number_format($order->paid_amount, 2) }}</td>
                                <td class="text-{{ ($order->total_amount - $order->paid_amount) > 0 ? 'danger' : 'success' }}">
                                    <strong>${{ number_format($order->total_amount - $order->paid_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->status == 'received' ? 'success' : ($order->status == 'confirmed' ? 'primary' : 'secondary') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    .btn, form {
        display: none !important;
    }
    .card-header {
        background-color: #f8f9fa !important;
        color: #000 !important;
    }
}
</style>
@endsection