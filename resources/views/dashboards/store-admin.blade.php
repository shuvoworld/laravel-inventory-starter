@extends('layouts.app')

@section('title', __('common.dashboard'))
@section('page-title', __('common.dashboard'))

@php
function formatMoney($amount) {
    $currencySettings = \App\Modules\StoreSettings\Models\StoreSetting::getCurrencySettings();
    return ($currencySettings['symbol'] ?? '$') . number_format($amount, 2);
}
@endphp

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line text-primary me-2"></i>
                Store Dashboard
            </h1>
            <p class="text-muted mb-0">{{ $companyInfo['name'] ?? 'Store Analytics' }} • {{ now()->format('F j, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <!-- Current Stock Value Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100 bg-gradient-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-dark mb-1 small">Current Stock Value</p>
                            <h3 class="mb-0 text-dark">{{ formatMoney($stockValue) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-2 rounded">
                            <i class="fas fa-boxes text-dark"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <small class="text-muted">{{ $stockSummary['total_products'] }} products</small>
                        @if($stockGrowth >= 0)
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>{{ number_format($stockGrowth, 1) }}%
                            </small>
                        @else
                            <small class="text-danger">
                                <i class="fas fa-arrow-down me-1"></i>{{ number_format(abs($stockGrowth), 1) }}%
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost of Goods Sold Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Cost of Goods Sold</p>
                            <h3 class="mb-0">{{ formatMoney($currentMonthData['cogs']) }}</h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-2 rounded">
                            <i class="fas fa-box-open text-danger"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted small">{{ number_format($currentMonthData['gross_profit_margin'], 1) }}% margin</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100 {{ count($lowStockProducts) > 0 ? 'bg-warning' : 'bg-secondary' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-dark mb-1 small">Low Stock Items</p>
                            <h3 class="mb-0 text-dark">{{ count($lowStockProducts) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-20 p-2 rounded">
                            <i class="fas fa-exclamation-triangle text-dark"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <small class="text-muted">{{ $stockSummary['out_of_stock_products'] }} out of stock</small>
                        @can('products.view')
                            <a href="{{ route('modules.products.index') }}" class="text-dark text-decoration-none">
                                <small>View All <i class="fas fa-arrow-right ms-1"></i></small>
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="text-muted mb-1 small">Net Profit</p>
                            <h3 class="mb-0">{{ formatMoney($currentMonthData['net_profit']) }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded">
                            <i class="fas fa-chart-line text-success"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($profitGrowth >= 0)
                            <i class="fas fa-arrow-up text-success me-1 small"></i>
                            <span class="text-success small">{{ number_format($profitGrowth, 1) }}%</span>
                        @else
                            <i class="fas fa-arrow-down text-danger me-1 small"></i>
                            <span class="text-danger small">{{ number_format(abs($profitGrowth), 1) }}%</span>
                        @endif
                        <span class="text-muted ms-2 small">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit and Loss Reports -->
    <div class="row g-3">
        <!-- Today's Profit and Loss -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-day text-primary me-2"></i>
                        Today's Profit and Loss Report
                    </h5>
                    <small class="text-muted">{{ now()->format('F j, Y') }}</small>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <!-- Income -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-primary py-2">Income</td>
                                <td class="text-end py-2 fw-bold">{{ formatMoney($todayData['income']) }}</td>
                            </tr>

                            <!-- Cost of Goods Sold -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-primary py-2">Cost of Goods Sold</td>
                                <td class="text-end py-2 fw-bold text-danger">({{ formatMoney($todayData['cogs']) }})</td>
                            </tr>

                            <!-- Operating Expenses -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-primary py-2">Operating Expenses</td>
                                <td class="text-end py-2 fw-bold text-warning">({{ formatMoney($todayData['operating_expenses']) }})</td>
                            </tr>

                            <!-- Net Profit/Loss -->
                            <tr class="border-top border-2 border-dark">
                                <td class="fw-bold py-3 fs-6">Net Profit / (Loss)</td>
                                <td class="text-end py-3 fw-bold fs-5 {{ $todayData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatMoney($todayData['net_profit']) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- This Month's Profit and Loss -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        This Month's Profit and Loss Report
                    </h5>
                    <small class="text-muted">{{ now()->format('F Y') }}</small>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <!-- Income -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-primary py-2">Income</td>
                                <td class="text-end py-2 fw-bold">{{ formatMoney($monthData['income']) }}</td>
                            </tr>

                            <!-- Cost of Goods Sold -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-primary py-2">Cost of Goods Sold</td>
                                <td class="text-end py-2 fw-bold text-danger">({{ formatMoney($monthData['cogs']) }})</td>
                            </tr>

                            <!-- Operating Expenses -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-primary py-2">Operating Expenses</td>
                                <td class="text-end py-2 fw-bold text-warning">({{ formatMoney($monthData['operating_expenses']) }})</td>
                            </tr>

                            <!-- Net Profit/Loss -->
                            <tr class="border-top border-2 border-dark">
                                <td class="fw-bold py-3 fs-6">Net Profit / (Loss)</td>
                                <td class="text-end py-3 fw-bold fs-5 {{ $monthData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ formatMoney($monthData['net_profit']) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
