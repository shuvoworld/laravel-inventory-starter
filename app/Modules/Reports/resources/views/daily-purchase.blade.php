@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush
@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
@endpush

@section('title', 'Daily Purchase Report')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Daily Purchase Report</h1>
                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('modules.reports.daily-purchase') }}" class="d-flex gap-2">
                        <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="form-control">
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                    <a href="{{ route('modules.reports.daily-purchase') }}" class="btn btn-outline-secondary">Today</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Amount</h5>
                    <h2 class="card-text">${{ $report['formatted_metrics']['total_amount'] ?? '0.00' }}</h2>
                    <small class="text-white-50">{{ $report['report_date'] ?? 'Today' }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Purchase Orders</h5>
                    <h2 class="card-text">{{ $report['formatted_metrics']['total_transactions'] ?? '0' }}</h2>
                    <small class="text-white-50">Total Orders</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Avg Order Value</h5>
                    <h2 class="card-text">${{ $report['formatted_metrics']['average_transaction_value'] ?? '0.00' }}</h2>
                    <small class="text-white-50">Per Order</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Discount</h5>
                    <h2 class="card-text">${{ $report['formatted_metrics']['total_discount'] ?? '0.00' }}</h2>
                    <small class="text-white-50">All Discounts</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Products -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 5 Purchased Products</h5>
                </div>
                <div class="card-body">
                    @if(isset($report['top_products']) && $report['top_products']->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th class="text-end">Quantity</th>
                                        <th class="text-end">Orders</th>
                                        <th class="text-end">Total Cost</th>
                                        <th class="text-end">Avg Unit Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($report['top_products'] as $product)
                                        <tr>
                                            <td>{{ $product->product_name ?? 'Unknown Product' }}</td>
                                            <td class="text-end">{{ $product->total_quantity ?? 0 }}</td>
                                            <td class="text-end">{{ $product->orders_count ?? 0 }}</td>
                                            <td class="text-end">${{ number_format($product->total_cost ?? 0, 2) }}</td>
                                            <td class="text-end">${{ number_format($product->avg_unit_price ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No purchases recorded for this date.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- 30-Day Purchase Details -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">30-Day Purchase Summary</h5>
                    <div>
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="printDataTable()">
                            <i class="fas fa-print me-1"></i> Print
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="purchaseDataTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Discount</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Orders</th>
                                    <th class="text-end">Avg Order Value</th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthlyTrends as $trend)
                                    <tr>
                                        <td data-order="{{ $trend['date_full'] ?? '' }}">{{ $trend['date'] ?? 'N/A' }}</td>
                                        <td class="text-end" data-order="{{ $trend['subtotal'] ?? 0 }}">${{ number_format($trend['subtotal'] ?? 0, 2) }}</td>
                                        <td class="text-end text-danger" data-order="{{ $trend['discount'] ?? 0 }}">-${{ number_format($trend['discount'] ?? 0, 2) }}</td>
                                        <td class="text-end" data-order="{{ $trend['tax'] ?? 0 }}">${{ number_format($trend['tax'] ?? 0, 2) }}</td>
                                        <td class="text-end" data-order="{{ $trend['amount'] ?? 0 }}"><strong>${{ number_format($trend['amount'] ?? 0, 2) }}</strong></td>
                                        <td class="text-end" data-order="{{ $trend['transactions'] ?? 0 }}">{{ $trend['transactions'] ?? 0 }}</td>
                                        <td class="text-end" data-order="{{ $trend['avg_value'] ?? 0 }}">${{ number_format($trend['avg_value'] ?? 0, 2) }}</td>
                                        <td data-order="{{ $trend['amount'] ?? 0 }}">
                                            @if(($trend['transactions'] ?? 0) == 0)
                                                <span class="badge bg-secondary">No Purchases</span>
                                            @elseif(($trend['amount'] ?? 0) >= 10000)
                                                <span class="badge bg-success">Excellent</span>
                                            @elseif(($trend['amount'] ?? 0) >= 5000)
                                                <span class="badge bg-primary">Good</span>
                                            @elseif(($trend['amount'] ?? 0) >= 1000)
                                                <span class="badge bg-info">Moderate</span>
                                            @else
                                                <span class="badge bg-warning">Low</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <a href="{{ route('modules.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    $('#purchaseDataTable').DataTable({
        responsive: true,
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
        order: [[0, 'desc']], // Sort by date descending by default
        language: {
            search: "Search records:",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ records",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        },
        columnDefs: [
            { targets: [1, 2, 3, 4, 5, 6], className: "text-end" },
            { targets: 2, className: "text-danger" } // Discount column
        ]
    });
});

// Export to Excel function
function exportToExcel() {
    const table = document.getElementById('purchaseDataTable');
    const rows = table.getElementsByTagName('tr');
    let csv = [];

    // Headers
    const headers = ['Date', 'Subtotal', 'Discount', 'Tax', 'Total Amount', 'Orders', 'Avg Order Value', 'Performance'];
    csv.push(headers.join(','));

    // Data rows (skip header row)
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');
        const rowData = [];

        for (let j = 0; j < cells.length; j++) {
            // Get cell text and clean for CSV
            let cellText = cells[j].innerText || cells[j].textContent;
            cellText = cellText.replace(/"/g, '""'); // Escape quotes
            cellText = cellText.replace(/(\r\n|\n|\r)/gm, ""); // Remove line breaks
            rowData.push('"' + cellText + '"');
        }
        csv.push(rowData.join(','));
    }

    // Create download link
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);

    link.setAttribute('href', url);
    link.setAttribute('download', 'daily_purchase_30_days.csv');
    link.style.visibility = 'hidden';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Print datatable function
function printDataTable() {
    const table = $('#salesDataTable').DataTable();
    table.buttons().container().remove();

    window.print();
}
</script>

<style>
@media print {
    .btn, .d-flex.justify-content-between, .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
        display: none !important;
    }

    .card {
        page-break-inside: avoid;
    }

    .container-fluid {
        max-width: 100%;
        padding: 0;
    }
}
</style>
@endsection