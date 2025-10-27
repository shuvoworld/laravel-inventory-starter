@extends('layouts.module')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Supplier Due Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('modules.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Supplier Due Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filters</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('modules.reports.supplier-due') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>All</option>
                                    <option value="pending" {{ $filters['status'] == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="overdue" {{ $filters['status'] == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="paid" {{ $filters['status'] == 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="supplier_id">Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-control">
                                    <option value="">All Suppliers</option>
                                    @foreach($allSuppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ $filters['supplier_id'] == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="start_date">Order Date From</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                       value="{{ $filters['start_date'] }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="end_date">Order Date To</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                       value="{{ $filters['end_date'] }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Filter
                                    </button>
                                    <a href="{{ route('modules.reports.supplier-due') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="due_date_from">Due Date From</label>
                                <input type="date" name="due_date_from" id="due_date_from" class="form-control"
                                       value="{{ $filters['due_date_from'] }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="due_date_to">Due Date To</label>
                                <input type="date" name="due_date_to" id="due_date_to" class="form-control"
                                       value="{{ $filters['due_date_to'] }}">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>${{ number_format($summary['total_due'], 2) }}</h3>
                        <p>Total Due</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>${{ number_format($summary['total_overdue'], 2) }}</h3>
                        <p>Overdue Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>${{ number_format($summary['total_pending'], 2) }}</h3>
                        <p>Pending Amount</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $summary['supplier_count'] }}</h3>
                        <p>Suppliers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Supplier Summary -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Supplier Summary</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Supplier</th>
                                <th>Total Due</th>
                                <th>Overdue</th>
                                <th>Pending</th>
                                <th>Orders</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suppliers as $supplierData)
                            <tr>
                                <td>{{ $supplierData['supplier']->name }}</td>
                                <td class="text-right">
                                    <strong>${{ number_format($supplierData['total_due'], 2) }}</strong>
                                </td>
                                <td class="text-right text-danger">
                                    ${{ number_format($supplierData['overdue'], 2) }}
                                </td>
                                <td class="text-right text-warning">
                                    ${{ number_format($supplierData['pending'], 2) }}
                                </td>
                                <td class="text-center">{{ $supplierData['order_count'] }}</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="toggleSupplierDetails({{ $supplierData['supplier']->id }})">
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No supplier records found</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <th>Total</th>
                                <th class="text-right">
                                    <strong>${{ number_format($summary['total_due'], 2) }}</strong>
                                </th>
                                <th class="text-right text-danger">
                                    ${{ number_format($summary['total_overdue'], 2) }}
                                </th>
                                <th class="text-right text-warning">
                                    ${{ number_format($summary['total_pending'], 2) }}
                                </th>
                                <th class="text-center">{{ $purchaseOrders->count() }}</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detailed Orders -->
        @foreach($suppliers as $supplierId => $supplierData)
        <div id="supplier-details-{{ $supplierId }}" class="card" style="display: none;">
            <div class="card-header">
                <h4 class="card-title">
                    {{ $supplierData['supplier']->name }} - Order Details
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Order Date</th>
                                <th>Due Date</th>
                                <th>Total Amount</th>
                                <th>Paid Amount</th>
                                <th>Due Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrders->where('supplier_id', $supplierId) as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->order_date->format('M d, Y') }}</td>
                                <td>{{ $order->due_date->format('M d, Y') }}</td>
                                <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="text-right">${{ number_format($order->paid_amount, 2) }}</td>
                                <td class="text-right">
                                    <strong>${{ number_format($order->total_amount - $order->paid_amount, 2) }}</strong>
                                </td>
                                <td>
                                    @if($order->due_date < now() && $order->payment_status !== 'paid')
                                        <span class="badge badge-danger">Overdue</span>
                                    @else
                                        <span class="badge badge-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('modules.purchase-order.show', $order->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>

@push('scripts')
<script>
function toggleSupplierDetails(supplierId) {
    const detailsDiv = document.getElementById('supplier-details-' + supplierId);
    if (detailsDiv.style.display === 'none') {
        detailsDiv.style.display = 'block';
    } else {
        detailsDiv.style.display = 'none';
    }
}

// Auto-hide print elements when printing
window.addEventListener('beforeprint', function() {
    document.querySelectorAll('.no-print').forEach(el => el.style.display = 'none');
});

window.addEventListener('afterprint', function() {
    document.querySelectorAll('.no-print').forEach(el => el.style.display = '');
});
</script>
@endpush
@endsection