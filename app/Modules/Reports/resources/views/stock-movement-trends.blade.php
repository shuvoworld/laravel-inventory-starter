@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Stock Movement Trends</h1>
    <a href="{{ route('modules.reports.stock') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Stock Report
    </a>
</div>

<!-- Date Range Selection -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Date Range & Period Type</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('modules.reports.stock.movement-trends') }}">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="period_type" class="form-label">Period Type</label>
                    <select id="period_type" name="period_type" class="form-select" onchange="this.form.submit()">
                        <option value="day" {{ $periodType === 'day' ? 'selected' : '' }}>Daily</option>
                        <option value="week" {{ $periodType === 'week' ? 'selected' : '' }}>Weekly</option>
                        <option value="month" {{ $periodType === 'month' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sync me-1"></i> Update Report
                    </button>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-success w-100" onclick="exportData()">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Total Stock In</h5>
                <h3>${{ number_format(collect($trends)->sum('purchases_in'), 2) }}</h3>
                <small>{{ number_format(collect($trends)->sum('total_products_purchased')) }} units</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5 class="card-title">Total Stock Out</h5>
                <h3>${{ number_format(collect($trends)->sum('sales_out'), 2) }}</h3>
                <small>{{ number_format(collect($trends)->sum('total_products_sold')) }} units</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Net Change</h5>
                <h3>
                    @php
                    $netChange = collect($trends)->sum('net_change');
                    $changeClass = $netChange >= 0 ? '' : '-';
                    @endphp
                    {{ $changeClass }}${{ number_format(abs($netChange), 2) }}
                </h3>
                <small>{{ $netChange >= 0 ? 'Positive' : 'Negative' }} movement</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Average Daily</h5>
                <h3>${{ number_format(collect($trends)->avg('purchases_in'), 2) }}</h3>
                <small>Stock in per {{ $periodType === 'day' ? 'day' : ($periodType === 'week' ? 'week' : 'month') }}</small>
            </div>
        </div>
    </div>
</div>

<!-- Movement Trends Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">Stock Movement Trends</h5>
        <p class="text-muted mb-0">Showing {{ $startDate->format('M j, Y') }} - {{ $endDate->format('M j, Y') }}</p>
    </div>
    <div class="card-body">
        <canvas id="movementChart" height="100"></canvas>
    </div>
</div>

<!-- Detailed Movement Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Detailed Movement Data</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>{{ ucfirst($periodType) }}</th>
                        <th class="text-end">Stock In (Value)</th>
                        <th class="text-end">Stock Out (Value)</th>
                        <th class="text-end">Net Change</th>
                        <th class="text-center">Units In</th>
                        <th class="text-center">Units Out</th>
                        <th class="text-center">Net Units</th>
                        <th class="text-end">Average Value/Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trends as $trend)
                    <tr>
                        <td>{{ Carbon::parse($trend['date'])->format($periodType === 'day' ? 'M j, Y' : ($periodType === 'week' ? 'M j' : 'M Y')) }}</td>
                        <td class="text-end text-success">
                            +${{ number_format($trend['purchases_in'], 2) }}
                        </td>
                        <td class="text-end text-danger">
                            -${{ number_format($trend['sales_out'], 2) }}
                        </td>
                        <td class="text-end fw-bold {{ $trend['net_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $trend['net_change'] >= 0 ? '+' : '' }}${{ number_format($trend['net_change'], 2) }}
                        </td>
                        <td class="text-center">+{{ $trend['total_products_purchased'] }}</td>
                        <td class="text-center">{{ $trend['total_products_sold'] }}</td>
                        <td class="text-center fw-bold {{ $trend['total_products_purchased'] - $trend['total_products_sold'] >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $trend['total_products_purchased'] - $trend['total_products_sold'] >= 0 ? '+' : '' }}{{ $trend['total_products_purchased'] - $trend['total_products_sold'] }}
                        </td>
                        <td class="text-end">
                            ${{ number_format($trend['total_products_sold'] > 0 ? $trend['sales_out'] / $trend['total_products_sold'] : 0, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary">
                    <tr class="fw-bold">
                        <td>Totals</td>
                        <td class="text-end text-success">+${{ number_format(collect($trends)->sum('purchases_in'), 2) }}</td>
                        <td class="text-end text-danger">-${{ number_format(collect($trends)->sum('sales_out'), 2) }}</td>
                        <td class="text-end {{ collect($trends)->sum('net_change') >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ collect($trends)->sum('net_change') >= 0 ? '+' : '' }}${{ number_format(collect($trends)->sum('net_change'), 2) }}
                        </td>
                        <td class="text-center">+{{ number_format(collect($trends)->sum('total_products_purchased')) }}</td>
                        <td class="text-center">{{ number_format(collect($trends)->sum('total_products_sold')) }}</td>
                        <td class="text-center {{ collect($trends)->sum('total_products_purchased') - collect($trends)->sum('total_products_sold') >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ collect($trends)->sum('total_products_purchased') - collect($trends)->sum('total_products_sold') >= 0 ? '+' : '' }}{{ collect($trends)->sum('total_products_purchased') - collect($trends)->sum('total_products_sold') }}
                        </td>
                        <td class="text-end">
                            ${{ number_format(collect($trends)->sum('total_products_sold') > 0 ? collect($trends)->sum('sales_out') / collect($trends)->sum('total_products_sold') : 0, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('movementChart').getContext('2d');
const trendsData = @json($trends);

const labels = trendsData.map(item => {
    const date = new Date(item.date);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
});

const movementChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Stock In',
                data: trendsData.map(item => item.purchases_in),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1,
                fill: true
            },
            {
                label: 'Stock Out',
                data: trendsData.map(item => item.sales_out),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.1,
                fill: true
            },
            {
                label: 'Net Change',
                data: trendsData.map(item => item.net_change),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.1,
                fill: false,
                borderDash: [5, 5]
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                    }
                }
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
        }
    }
});

function exportData() {
    const csvContent = [
        ['Date', 'Stock In ($)', 'Stock Out ($)', 'Net Change ($)', 'Units In', 'Units Out', 'Net Units'],
        ...trendsData.map(item => [
            new Date(item.date).toLocaleDateString(),
            item.purchases_in.toFixed(2),
            item.sales_out.toFixed(2),
            item.net_change.toFixed(2),
            item.total_products_purchased,
            item.total_products_sold,
            item.total_products_purchased - item.total_products_sold
        ])
    ].map(row => row.join(',')).join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `stock_movement_trends_{{ date('Y-m-d') }}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush