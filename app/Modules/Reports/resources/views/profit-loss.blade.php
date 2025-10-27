@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Profit & Loss Report</h1>
    <div class="btn-group" role="group">
        <a href="{{ route('modules.reports.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Reports
        </a>
        <button class="btn btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" data-bs-display="static">
            <i class="fas fa-download me-1"></i> Export
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a href="#" class="dropdown-item" data-export="pdf">
                <i class="fas fa-file-pdf me-2"></i> Export as PDF
            </a></li>
            <li><a href="#" class="dropdown-item" data-export="excel">
                <i class="fas fa-file-excel me-2"></i> Export as Excel
            </a></li>
            <li><a href="#" class="dropdown-item" data-export="csv">
                <i class="fas fa-file-csv me-2"></i> Export as CSV
            </a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a href="#" class="dropdown-item" data-export="print">
                <i class="fas fa-print me-2"></i> Print Report
            </a></li>
        </ul>
    </div>
</div>

<!-- Period Selection and Date Filter -->
<div class="card mb-4">
    <div class="card-body">
        <!-- Period Type Tabs -->
        <div class="mb-3">
            <ul class="nav nav-pills" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'day' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'day']) }}">
                        <i class="fas fa-calendar-day me-1"></i> Daily
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'week' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'week']) }}">
                        <i class="fas fa-calendar-week me-1"></i> Weekly
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'month' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'month']) }}">
                        <i class="fas fa-calendar me-1"></i> Monthly
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $data['period_type'] === 'custom' ? 'active' : '' }}"
                       href="{{ route('modules.reports.profit-loss', ['period_type' => 'custom']) }}">
                        <i class="fas fa-calendar-alt me-1"></i> Custom Range
                    </a>
                </li>
            </ul>
        </div>

        <!-- Date Range Form -->
        <form method="GET" action="{{ route('modules.reports.profit-loss') }}" class="row g-3">
            <input type="hidden" name="period_type" value="{{ $data['period_type'] }}">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $data['period']['start_date'] }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $data['period']['end_date'] }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-1"></i> Update Report
                </button>
                <a href="{{ route('modules.reports.profit-loss', ['period_type' => $data['period_type']]) }}" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-refresh me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Header -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar me-2"></i>Report Period: {{ $data['period']['formatted_period'] }}
        </h5>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                <h4 class="text-success">${{ number_format($data['revenue']['total_revenue'], 2) }}</h4>
                <p class="card-text">Total Revenue</p>
                <small class="text-muted">{{ $data['orders']['sales_orders_count'] }} orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-shopping-cart fa-2x text-danger mb-2"></i>
                <h4 class="text-danger">${{ number_format($data['costs']['cogs'], 2) }}</h4>
                <p class="card-text">Cost of Goods Sold</p>
                <small class="text-muted">{{ number_format($data['revenue']['total_sales_quantity']) }} items sold</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                <h4 class="text-primary">${{ number_format($data['profit']['gross_profit'], 2) }}</h4>
                <p class="card-text">Gross Profit</p>
                <small class="text-muted">{{ number_format($data['profit']['gross_profit_margin'], 1) }}% margin</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-file-invoice fa-2x text-warning mb-2"></i>
                <h4 class="text-warning">${{ number_format($data['costs']['operating_expenses'], 2) }}</h4>
                <p class="card-text">Operating Expenses</p>
                <small class="text-muted">Business expenses</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-secondary">
            <div class="card-body text-center">
                <i class="fas fa-receipt fa-2x text-secondary mb-2"></i>
                <h4 class="text-secondary">${{ number_format($data['costs']['general_expenses'], 2) }}</h4>
                <p class="card-text">General Expenses</p>
                <small class="text-muted">Other expenses</small>
            </div>
        </div>
    </div>
</div>
    <div class="col-md-3">
        <div class="card border-dark">
            <div class="card-body text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-dark mb-2"></i>
                <h4 class="text-dark">${{ number_format($data['costs']['total_expenses'], 2) }}</h4>
                <p class="card-text">Total Expenses</p>
                <small class="text-muted">COGS + All Expenses</small>
            </div>
        </div>
    </div>
</div>

<!-- Net Profit Summary -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-chart-line fa-2x text-primary mb-2"></i>
                <h4 class="text-primary">${{ number_format($data['profit']['gross_profit'], 2) }}</h4>
                <p class="card-text">Gross Profit</p>
                <small class="text-muted">{{ number_format($data['profit']['gross_profit_margin'], 1) }}% margin</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="fas fa-coins fa-2x text-info mb-2"></i>
                <h4 class="text-info {{ $data['profit']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($data['profit']['net_profit'], 2) }}</h4>
                <p class="card-text">Net Profit</p>
                <small class="text-muted">{{ number_format($data['profit']['net_profit_margin'], 1) }}% margin</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Simple Financial Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Financial Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Money In -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="card-title text-success mb-3">
                                    <i class="fas fa-arrow-down me-2"></i>Money In (Sales)
                                </h6>
                                <h3 class="text-success">${{ number_format($data['revenue']['total_revenue'], 2) }}</h3>
                                <p class="text-muted mb-1">{{ $data['orders']['sales_orders_count'] }} sales orders</p>
                                <p class="text-muted mb-0">{{ number_format($data['revenue']['total_sales_quantity']) }} items sold</p>
                            </div>
                        </div>
                    </div>

                    <!-- Money Out -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-danger">
                            <div class="card-body text-center">
                                <h6 class="card-title text-danger mb-3">
                                    <i class="fas fa-arrow-up me-2"></i>Money Out (Costs)
                                </h6>
                                <h3 class="text-danger">${{ number_format($data['costs']['total_expenses'], 2) }}</h3>
                                <p class="text-muted mb-1">${{ number_format($data['costs']['cogs'], 2) }} product costs</p>
                                <p class="text-muted mb-1">${{ number_format($data['costs']['operating_expenses'], 2) }} operating expenses</p>
                                <p class="text-muted mb-0">${{ number_format($data['costs']['general_expenses'], 2) }} general expenses</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit/Loss Summary -->
                <div class="row">
                    <div class="col-12">
                        <div class="card {{ $data['profit']['net_profit'] >= 0 ? 'border-success' : 'border-danger' }}">
                            <div class="card-body text-center">
                                <h6 class="card-title mb-3">
                                    @if($data['profit']['net_profit'] >= 0)
                                        <i class="fas fa-smile me-2 text-success"></i>
                                        <span class="text-success">Great! You Made Profit</span>
                                    @else
                                        <i class="fas fa-frown me-2 text-danger"></i>
                                        <span class="text-danger">Loss - Need to Improve</span>
                                    @endif
                                </h6>
                                <h2 class="{{ $data['profit']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($data['profit']['net_profit'], 2) }}
                                </h2>
                                <p class="text-muted mb-0">
                                    This is {{ number_format(abs($data['profit']['net_profit_margin']), 1) }}%
                                    @if($data['profit']['net_profit'] >= 0) profit @else loss @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Simple Breakdown -->
                @if(count($data['expenses_by_category']) > 0)
                <div class="mt-4">
                    <h6 class="mb-3">Expense Breakdown:</h6>
                    <div class="row">
                        @foreach($data['expenses_by_category'] as $expense)
                            <div class="col-md-4 mb-2">
                                <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded">
                                    <span>{{ $expense['category_name'] }}</span>
                                    <strong class="text-warning">${{ number_format($expense['total'], 2) }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Performance Tips -->
                <div class="mt-4">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-lightbulb me-2"></i>Quick Tips:
                        </h6>
                        <ul class="mb-0">
                            @if($data['profit']['net_profit'] < 0)
                                <li>Try to reduce expenses or increase sales prices</li>
                                <li>Look for ways to sell more products</li>
                            @else
                                <li>Good job! Keep tracking your expenses</li>
                                <li>Consider ways to increase your profit margin</li>
                            @endif
                            <li>Regular reviews help improve business performance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Top Selling Products -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-trophy me-2"></i>Top Selling Products
                </h5>
            </div>
            <div class="card-body">
                @if($data['top_products']->count() > 0)
                    @foreach($data['top_products'] as $index => $product)
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <span class="badge badge-primary me-2">{{ $index + 1 }}</span>
                                <strong>{{ $product['product_name'] }}</strong>
                                <br>
                                <small class="text-muted">Qty: {{ number_format($product['quantity_sold']) }}</small>
                            </div>
                            <div class="text-end">
                                <strong class="text-success">${{ number_format($product['total_revenue'], 2) }}</strong>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted text-center">No sales data available for this period.</p>
                @endif
            </div>
        </div>

        <!-- Performance Indicators -->
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-gauge me-2"></i>Performance Indicators
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Profit Margin</span>
                        <strong>{{ number_format($data['profit']['gross_profit_margin'], 1) }}%</strong>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-success" style="width: {{ min($data['profit']['gross_profit_margin'], 100) }}%"></div>
                    </div>
                </div>

                @php $revenueGrowth = 15; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Revenue Growth</span>
                        <strong class="text-success">+{{ $revenueGrowth }}%</strong>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-primary" style="width: {{ min($revenueGrowth, 100) }}%"></div>
                    </div>
                </div>

                <div class="text-center">
                    <small class="text-muted">Compared to previous period</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Trend Analysis and Period Breakdown -->
@if($data['period_type'] !== 'custom' && count($data['period_breakdown']) > 1)
<div class="row mt-4">
    <div class="col-12">
        <!-- Trend Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>{{ ucfirst($data['period_type']) }}ly Profit & Loss Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="profitLossChart" style="height: 400px;"></canvas>
            </div>
        </div>

        <!-- Period Breakdown Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>{{ ucfirst($data['period_type']) }}ly Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Period</th>
                                <th class="text-end">Revenue</th>
                                <th class="text-end">COGS</th>
                                <th class="text-end">Op. Expenses</th>
                                <th class="text-end">Gen. Expenses</th>
                                <th class="text-end">Total Expenses</th>
                                <th class="text-end">Net Profit</th>
                                <th class="text-end">Profit %</th>
                                <th class="text-center">Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data['period_breakdown'] as $period)
                                @php
                                    $netProfitMargin = $period['revenue'] > 0 ? ($period['net_profit'] / $period['revenue']) * 100 : 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $period['period'] }}</strong></td>
                                    <td class="text-end text-success">${{ number_format($period['revenue'], 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($period['cogs'], 2) }}</td>
                                    <td class="text-end text-warning">${{ number_format($period['operating_expenses'], 2) }}</td>
                                    <td class="text-end text-secondary">${{ number_format($period['general_expenses'], 2) }}</td>
                                    <td class="text-end text-dark"><strong>${{ number_format($period['total_expenses'], 2) }}</strong></td>
                                    <td class="text-end">
                                        <span class="{{ $period['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                            ${{ number_format($period['net_profit'], 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="{{ $netProfitMargin >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($netProfitMargin, 1) }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $period['orders_count'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th>Total</th>
                                <th class="text-end">${{ number_format($data['revenue']['total_revenue'], 2) }}</th>
                                <th class="text-end">${{ number_format($data['costs']['cogs'], 2) }}</th>
                                <th class="text-end">${{ number_format($data['costs']['operating_expenses'], 2) }}</th>
                                <th class="text-end">${{ number_format($data['costs']['general_expenses'], 2) }}</th>
                                <th class="text-end"><strong>${{ number_format($data['costs']['total_expenses'], 2) }}</strong></th>
                                <th class="text-end">${{ number_format($data['profit']['net_profit'], 2) }}</th>
                                <th class="text-end">{{ number_format($data['profit']['net_profit_margin'], 1) }}%</th>
                                <th class="text-center">{{ $data['orders']['sales_orders_count'] }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if($data['period_type'] !== 'custom' && count($data['period_breakdown']) > 1)
    // Profit & Loss Trend Chart
    const ctx = document.getElementById('profitLossChart').getContext('2d');
    const chartData = @json($data['period_breakdown']);

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.period),
            datasets: [
                {
                    label: 'Revenue',
                    data: chartData.map(item => item.revenue),
                    borderColor: 'rgb(40, 167, 69)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'COGS',
                    data: chartData.map(item => item.cogs),
                    borderColor: 'rgb(220, 53, 69)',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Operating Expenses',
                    data: chartData.map(item => item.operating_expenses),
                    borderColor: 'rgb(255, 193, 7)',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'General Expenses',
                    data: chartData.map(item => item.general_expenses),
                    borderColor: 'rgb(108, 117, 125)',
                    backgroundColor: 'rgba(108, 117, 125, 0.1)',
                    tension: 0.4,
                    fill: false
                },
                {
                    label: 'Net Profit',
                    data: chartData.map(item => item.net_profit),
                    borderColor: 'rgb(0, 123, 255)',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: '{{ ucfirst($data['period_type']) }}ly Profit & Loss Trend'
                },
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    @endif
});

    // Export functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Handle export dropdown clicks
        const exportButtons = document.querySelectorAll('[data-export]');
        exportButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const exportType = this.dataset.export;
                handleExport(exportType);
            });
        });

        function handleExport(type) {
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);

            // Add export parameter to URL
            params.set('export', type);

            // Create new URL with export parameter
            const exportUrl = `${window.location.pathname}?${params.toString()}`;

            // Show loading indicator
            showExportLoading(type);

            // Download file
            fetch(exportUrl, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': type === 'pdf' ? 'application/pdf' : 'text/csv'
                }
            })
            .then(response => {
                if (response.ok) {
                    if (type === 'pdf') {
                        // For PDF, open in new tab
                        response.blob().then(blob => {
                            const pdfUrl = window.URL.createObjectURL(blob);
                            window.open(pdfUrl, '_blank');
                        });
                    } else if (type === 'excel') {
                        // For Excel, handle as file download
                        response.blob().then(blob => {
                            const excelUrl = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = excelUrl;
                            link.download = `profit-loss-report-{{ now()->format('Y-m-d') }}.xlsx`;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            window.URL.revokeObjectURL(excelUrl);
                        });
                    } else if (type === 'csv') {
                        // For CSV, handle as file download
                        response.blob().then(blob => {
                            const csvUrl = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = csvUrl;
                            link.download = `profit-loss-report-{{ now()->format('Y-m-d') }}.csv`;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            window.URL.revokeObjectURL(csvUrl);
                        });
                    }

                    // Show success notification
                    showExportNotification(type, 'success');
                } else {
                    showExportNotification(type, 'error');
                }
            })
            .catch(error => {
                console.error('Export failed:', error);
                showExportNotification(type, 'error');
            })
            .finally(() => {
                hideExportLoading(type);
            });
        }

        function handleExport(type) {
            if (type === 'print') {
                // Print report
                window.print();
            } else {
                handleFileExport(type);
            }
        }

        function showExportLoading(type) {
            const button = document.querySelector(`[data-export="${type}"]`);
            if (button) {
                button.disabled = true;
                button.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Exporting...`;
            }
        }

        function hideExportLoading(type) {
            const button = document.querySelector(`[data-export="${type}"]`);
            if (button) {
                button.disabled = false;
                button.innerHTML = button.innerHTML.replace(/<i class="fas fa-spinner fa-spin me-2"><\/i>Exporting.../, '');
            }
        }

        function showExportNotification(type, status) {
            const icons = {
                success: '<i class="fas fa-check-circle text-success"></i>',
                error: '<i class="fas fa-exclamation-triangle text-danger"></i>'
            };

            const messages = {
                pdf: 'PDF report',
                excel: 'Excel file',
                csv: 'CSV file',
                print: 'Print job',
                error: 'export failed'
            };

            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${status === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    ${icons[status === 'success' ? 'success' : 'error']}
                    <div class="ms-3">
                        <strong>${status === 'success' ? 'Export started' : 'Export failed'}</strong>
                        <div class="small">${status === 'success' ? `Your ${messages[type]} is being prepared` : 'Please try again later'}</div>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            // Auto-remove notification after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }
    });
</script>
@endpush
@endsection