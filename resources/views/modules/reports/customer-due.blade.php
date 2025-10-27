@extends('layouts.module')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Customer Due Report</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('modules.reports.index') }}">Reports</a></li>
                    <li class="breadcrumb-item active">Customer Due Report</li>
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
                <form method="GET" action="{{ route('modules.reports.customer-due') }}">
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
                                <label for="customer_id">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control">
                                    <option value="">All Customers</option>
                                    @foreach($allCustomers as $customer)
                                        <option value="{{ $customer->id }}" {{ $filters['customer_id'] == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
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
                                    <a href="{{ route('modules.reports.customer-due') }}" class="btn btn-secondary">
                                        <i class="fas fa-redo"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="due_date_from">Order Date From</label>
                                <input type="date" name="due_date_from" id="due_date_from" class="form-control"
                                       value="{{ $filters['due_date_from'] }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="due_date_to">Order Date To</label>
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
                        <h3>{{ $summary['customer_count'] }}</h3>
                        <p>Customers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Summary -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Customer Summary</h3>
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
                                <th>Customer</th>
                                <th>Total Due</th>
                                <th>Overdue</th>
                                <th>Pending</th>
                                <th>Orders</th>
                                <th>Last Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customerData)
                            <tr>
                                <td>
                                    <strong>{{ $customerData['customer']->name }}</strong><br>
                                    <small class="text-muted">{{ $customerData['customer']->email }}</small>
                                </td>
                                <td class="text-right">
                                    <strong>${{ number_format($customerData['total_due'], 2) }}</strong>
                                </td>
                                <td class="text-right text-danger">
                                    ${{ number_format($customerData['overdue'], 2) }}
                                </td>
                                <td class="text-right text-warning">
                                    ${{ number_format($customerData['pending'], 2) }}
                                </td>
                                <td class="text-center">{{ $customerData['order_count'] }}</td>
                                <td class="text-center">
                                    <small>{{ $customerData['last_order_date']->format('M d, Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="toggleCustomerDetails({{ $customerData['customer']->id }})">
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No customer records found</td>
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
                                <th class="text-center">{{ $salesOrders->count() }}</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Detailed Orders -->
        @foreach($customers as $customerId => $customerData)
        <div id="customer-details-{{ $customerId }}" class="card" style="display: none;">
            <div class="card-header">
                <h4 class="card-title">
                    {{ $customerData['customer']->name }} - Order Details
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Order Date</th>
                                <th>Total Amount</th>
                                <th>Paid Amount</th>
                                <th>Due Amount</th>
                                <th>Payment Status</th>
                                <th>Order Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesOrders->where('customer_id', $customerId) as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->order_date->format('M d, Y') }}</td>
                                <td class="text-right">${{ number_format($order->total_amount, 2) }}</td>
                                <td class="text-right">${{ number_format($order->paid_amount, 2) }}</td>
                                <td class="text-right">
                                    <strong>${{ number_format($order->total_amount - $order->paid_amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-{{
                                        $order->payment_status === 'paid' ? 'success' :
                                        ($order->payment_status === 'partial' ? 'warning' : 'secondary')
                                    }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{
                                        $order->status === 'delivered' ? 'success' :
                                        ($order->status === 'pending' ? 'secondary' : 'info')
                                    }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('modules.sales-order.show', $order->id) }}"
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('modules.sales-order.invoice', $order->id) }}"
                                           class="btn btn-outline-success"
                                           target="_blank">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Top Overdue Customers Alert -->
        @if($summary['overdue_count'] > 0)
        <div class="alert alert-danger">
            <h5><i class="fas fa-exclamation-triangle"></i> Payment Action Required</h5>
            <p>You have <strong>{{ $summary['overdue_count'] }}</strong> overdue orders with a total amount of
               <strong>${{ number_format($summary['total_overdue'], 2) }}</strong>.
               Consider following up with these customers for payment collection.</p>
        </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
function toggleCustomerDetails(customerId) {
    const detailsDiv = document.getElementById('customer-details-' + customerId);
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