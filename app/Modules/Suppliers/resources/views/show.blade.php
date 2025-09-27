@extends('layouts.adminlte')

@section('title', 'Supplier Details')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>{{ $supplier->name }} <span class="badge badge-{{ $supplier->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($supplier->status) }}</span></h1>
        <div>
            <a href="{{ route('modules.suppliers.edit', $supplier) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('modules.suppliers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Suppliers
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Contact Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Supplier Code:</strong></td>
                            <td>{{ $supplier->code }}</td>
                        </tr>
                        <tr>
                            <td><strong>Contact Person:</strong></td>
                            <td>{{ $supplier->contact_person ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>
                                @if($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Phone:</strong></td>
                            <td>
                                @if($supplier->phone)
                                    <a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td>{{ $supplier->full_address ?: 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Business Details</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Tax ID:</strong></td>
                            <td>{{ $supplier->tax_id ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Payment Terms:</strong></td>
                            <td>{{ $supplier->payment_terms ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Credit Limit:</strong></td>
                            <td>
                                @if($supplier->credit_limit)
                                    @php
                                        use App\Modules\StoreSettings\Models\StoreSetting;
                                        echo StoreSetting::formatCurrency($supplier->credit_limit);
                                    @endphp
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $supplier->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td>{{ $supplier->updated_at->format('M j, Y g:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($supplier->notes)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Notes</h3>
                </div>
                <div class="card-body">
                    <p>{{ $supplier->notes }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Purchase Orders</h3>
                    <div class="card-tools">
                        @can('purchase-orders.create')
                        <a href="{{ route('modules.purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Purchase Order
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    @if($supplier->purchaseOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>PO Number</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplier->purchaseOrders->take(10) as $po)
                                    <tr>
                                        <td>{{ $po->po_number }}</td>
                                        <td>{{ $po->order_date->format('M j, Y') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $po->status === 'completed' ? 'success' : ($po->status === 'pending' ? 'warning' : 'info') }}">
                                                {{ ucfirst($po->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                echo StoreSetting::formatCurrency($po->total_amount);
                                            @endphp
                                        </td>
                                        <td>
                                            @can('purchase-orders.view')
                                            <a href="{{ route('modules.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($supplier->purchaseOrders->count() > 10)
                        <div class="text-center mt-3">
                            @can('purchase-orders.view')
                            <a href="{{ route('modules.purchase-orders.index', ['supplier' => $supplier->id]) }}" class="btn btn-outline-primary">
                                View All Purchase Orders ({{ $supplier->purchaseOrders->count() }})
                            </a>
                            @endcan
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No purchase orders found for this supplier.</p>
                            @can('purchase-orders.create')
                            <a href="{{ route('modules.purchase-orders.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create First Purchase Order
                            </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Orders</span>
                    <span class="info-box-number">{{ $supplier->purchaseOrders->count() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending Orders</span>
                    <span class="info-box-number">{{ $supplier->pending_orders_count }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-dollar-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Purchase Value</span>
                    <span class="info-box-number">
                        @php
                            echo StoreSetting::formatCurrency($supplier->total_purchase_amount);
                        @endphp
                    </span>
                </div>
            </div>
        </div>
    </div>
@stop