@extends('layouts.module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Stock Valuation Report</h1>
    <a href="{{ route('modules.reports.stock') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Stock Report
    </a>
</div>

<!-- Valuation Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4>${{ number_format($valuation['total_cost_value'], 2) }}</h4>
                <p class="card-text mb-0">Total Cost Value</p>
                <small>Based on purchase costs</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4>${{ number_format($valuation['total_market_value'], 2) }}</h4>
                <p class="card-text mb-0">Total Market Value</p>
                <small>Based on selling prices</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4>${{ number_format($valuation['total_potential_profit'], 2) }}</h4>
                <p class="card-text mb-0">Potential Profit</p>
                <small>If all stock sold</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <h4>{{ number_format($valuation['overall_profit_margin'], 1) }}%</h4>
                <p class="card-text mb-0">Overall Margin</p>
                <small>Average profit margin</small>
            </div>
        </div>
    </div>
</div>

<!-- Profit Margin Distribution -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Value Breakdown</h5>
            </div>
            <div class="card-body">
                <canvas id="valueBreakdownChart" height="150"></canvas>
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        Cost Value: ${{ number_format($valuation['total_cost_value'], 2) }} |
                        Potential Profit: ${{ number_format($valuation['total_potential_profit'], 2) }}
                    </small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Inventory Metrics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3">
                            <h5 class="text-primary">${{ number_format($overview['summary']['total_stock_value'], 2) }}</h5>
                            <small class="text-muted">Current Stock Value</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3">
                            <h5 class="text-info">{{ number_format($overview['summary']['average_cost_per_unit'], 2) }}</h5>
                            <small class="text-muted">Avg Cost/Unit</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3">
                            <h5 class="text-success">{{ $overview['summary']['total_products'] }}</h5>
                            <small class="text-muted">Total Products</small>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="border rounded p-3">
                            <h5 class="text-warning">{{ number_format($overview['summary']['total_stock_quantity']) }}</h5>
                            <small class="text-muted">Total Units</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Brand Valuation Breakdown -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Valuation by Brand</h5>
        <p class="text-muted mb-0">Stock value and profit analysis by brand</p>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Brand</th>
                        <th class="text-center">Products</th>
                        <th class="text-end">Cost Value</th>
                        <th class="text-end">Market Value</th>
                        <th class="text-end">Potential Profit</th>
                        <th class="text-center">Profit Margin</th>
                        <th class="text-center">Avg Cost/Unit</th>
                        <th class="text-center">Value %</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $brandValuations = collect($valuation['valuation_by_brand'])
                        ->sortByDesc('cost_value')
                        ->take(10);
                    @endphp
                    @foreach($brandValuations as $brand => $data)
                    <tr>
                        <td class="fw-bold">{{ $brand }}</td>
                        <td class="text-center">{{ $data['product_count'] }}</td>
                        <td class="text-end">${{ number_format($data['cost_value'], 2) }}</td>
                        <td class="text-end">${{ number_format($data['market_value'], 2) }}</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $data['potential_profit'] > 0 ? 'success' : 'danger' }}">
                                ${{ number_format($data['potential_profit'], 2) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $data['market_value'] > 0 ? ($data['potential_profit'] / $data['market_value'] * 100) >= 20 ? 'success' : 'warning' : 'secondary' }}">
                                {{ number_format($data['market_value'] > 0 ? ($data['potential_profit'] / $data['market_value']) * 100 : 0, 1) }}%
                            </span>
                        </td>
                        <td class="text-end">
                            ${{ number_format($data['cost_value'] > 0 ? $data['cost_value'] / collect($overview['brands_breakdown'])[$brand]['total_quantity'] : 0, 2) }}
                        </td>
                        <td class="text-center">
                            {{ number_format(($data['cost_value'] / $valuation['total_cost_value']) * 100, 1) }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('valueBreakdownChart').getContext('2d');

const valueBreakdownChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Cost Value', 'Potential Profit'],
        datasets: [{
            data: [{{ $valuation['total_cost_value'] }}, {{ $valuation['total_potential_profit'] }}],
            backgroundColor: [
                'rgba(59, 130, 246, 0.8)',
                'rgba(34, 197, 94, 0.8)'
            ],
            borderColor: [
                'rgba(59, 130, 246, 1)',
                'rgba(34, 197, 94, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    font: {
                        size: 14
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let label = context.label || '';
                        let value = '$' + context.parsed.toLocaleString();
                        let percentage = ((context.parsed / ({{ $valuation['total_cost_value'] }} + {{ $valuation['total_potential_profit'] }})) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

function viewCategoryDetails(category) {
    // You can implement this to show a modal with detailed category information
    // or redirect to a filtered detailed stock view
    window.location.href = `{{ route('modules.reports.stock.detailed') }}?category=${encodeURIComponent(category)}`;
}

// Export functionality
function exportValuationReport() {
    const csvContent = [
        ['Brand', 'Products', 'Cost Value', 'Market Value', 'Potential Profit', 'Profit Margin %'],
        ...Object.entries({{ json_encode($valuation['valuation_by_brand']) }}).map(([brand, data]) => [
            brand,
            data.product_count,
            data.cost_value.toFixed(2),
            data.market_value.toFixed(2),
            data.potential_profit.toFixed(2),
            (data.market_value > 0 ? (data.potential_profit / data.market_value) * 100 : 0).toFixed(1)
        ]),
        ['TOTAL', '{{ collect($valuation["valuation_by_brand"])->sum("product_count") }}',
         '{{ $valuation["total_cost_value"] }}', '{{ $valuation["total_market_value"] }}',
         '{{ $valuation["total_potential_profit"] }}', '{{ $valuation["overall_profit_margin"] }}']
    ].map(row => row.join(',')).join('\n');

    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `stock_valuation_{{ date('Y-m-d') }}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
@endpush