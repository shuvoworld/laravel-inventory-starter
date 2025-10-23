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
            <h1 class="h3 mb-0 text-primary-custom">
                <i class="fas fa-chart-line me-2 text-primary-custom"></i>
                Store Dashboard
            </h1>
            <p class="text-secondary mb-0">
                <i class="fas fa-store me-1 text-secondary-custom"></i>{{ $companyInfo['name'] ?? 'Store Analytics' }}
                <span class="badge bg-primary-custom text-white ms-2">
                    <i class="fas fa-calendar me-1"></i>{{ now()->format('F j, Y') }}
                </span>
            </p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <!-- Current Stock Value Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card metric-card-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small text-white-50">Current Stock Value</p>
                            <h2 class="mb-0 text-white fw-bold">{{ formatMoney($stockValue) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-boxes text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge bg-white bg-opacity-20 text-white">
                            <i class="fas fa-cube me-1"></i>{{ $stockSummary['total_products'] }} products
                        </span>
                        @if($stockGrowth >= 0)
                            <span class="badge bg-success">
                                <i class="fas fa-arrow-up me-1"></i>{{ number_format($stockGrowth, 1) }}%
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="fas fa-arrow-down me-1"></i>{{ number_format(abs($stockGrowth), 1) }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Cost of Goods Sold Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card metric-card-accent h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small text-white-50">Cost of Goods Sold</p>
                            <h2 class="mb-0 text-white fw-bold">{{ formatMoney($currentMonthData['cogs']) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-box-open text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-white bg-opacity-20 text-white">
                            <i class="fas fa-percentage me-1"></i>{{ number_format($currentMonthData['gross_profit_margin'], 1) }}% margin
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small text-secondary">Low Stock Items</p>
                            <h2 class="mb-0 text-primary-custom fw-bold">{{ count($lowStockProducts) }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-exclamation-triangle text-primary-custom fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge bg-warning text-dark">
                            <i class="fas fa-times-circle me-1"></i>{{ $stockSummary['out_of_stock_products'] }} out of stock
                        </span>
                        @can('products.view')
                            <a href="{{ route('modules.products.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>View All
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Net Profit Card -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card metric-card-secondary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-1 small text-white-50">Net Profit</p>
                            <h2 class="mb-0 text-white fw-bold">{{ formatMoney($currentMonthData['net_profit']) }}</h2>
                        </div>
                        <div class="bg-white bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-chart-line text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($profitGrowth >= 0)
                            <span class="badge bg-success text-white me-2">
                                <i class="fas fa-arrow-up me-1"></i>{{ number_format($profitGrowth, 1) }}%
                            </span>
                        @else
                            <span class="badge bg-danger text-white me-2">
                                <i class="fas fa-arrow-down me-1"></i>{{ number_format(abs($profitGrowth), 1) }}%
                            </span>
                        @endif
                        <span class="badge bg-white bg-opacity-20 text-white">vs last month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Financial Summary -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <h5 class="mb-0 me-3 text-primary-custom">
                    <i class="fas fa-calendar-day me-2 text-primary-custom"></i>
                    Today's Financial Summary
                </h5>
                <span class="badge bg-primary-custom text-white">
                    <i class="fas fa-clock me-1"></i>{{ now()->format('l, M j, Y') }}
                </span>
            </div>
        </div>

        <!-- Today's Sales -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-primary-custom text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-white bg-opacity-20 text-white">
                            <i class="fas fa-calendar-day me-1"></i>Today
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-2 small text-white-50">Sales Revenue</p>
                            <h1 class="mb-0 text-white fw-bold">{{ formatMoney($todayData['income']) }}</h1>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas fa-shopping-cart text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-chart-line me-1"></i>Total sales today
                        </span>
                        <i class="fas fa-arrow-trend-up text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Purchases -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-accent-custom text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-white bg-opacity-20 text-white">
                            <i class="fas fa-calendar-day me-1"></i>Today
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-2 small text-white-50">Purchases</p>
                            <h1 class="mb-0 text-white fw-bold">{{ formatMoney($todayData['purchases']) }}</h1>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas fa-shopping-basket text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-clipboard-list me-1"></i>Purchase orders today
                        </span>
                        <i class="fas fa-box text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Expenses -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-dark bg-opacity-50 text-white">
                            <i class="fas fa-calendar-day me-1"></i>Today
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-2 small text-dark">Expenses</p>
                            <h1 class="mb-0 text-dark fw-bold">{{ formatMoney($todayData['operating_expenses']) }}</h1>
                        </div>
                        <div class="bg-dark bg-opacity-10 p-3 rounded-3">
                            <i class="fas fa-receipt text-dark fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-money-bill-wave me-1"></i>Operating expenses today
                        </span>
                        <i class="fas fa-chart-pie text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Profit/Loss -->
        <div class="col-xl-3 col-lg-6">
            <div class="card dashboard-card h-100">
                <div class="card-header {{ $todayData['net_profit'] >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-white bg-opacity-20 text-white">
                            <i class="fas fa-calendar-day me-1"></i>Today
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <p class="mb-2 small text-white-50">Today's {{ $todayData['net_profit'] >= 0 ? 'Profit' : 'Loss' }}</p>
                            <h1 class="mb-0 text-white fw-bold">{{ formatMoney($todayData['net_profit']) }}</h1>
                        </div>
                        <div class="bg-white bg-opacity-20 p-3 rounded-3">
                            <i class="fas {{ $todayData['net_profit'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }} text-white fa-lg"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="badge bg-white bg-opacity-20 {{ $todayData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                            @if($todayData['net_profit'] >= 0)
                                <i class="fas fa-check-circle me-1"></i>Profitable day
                            @else
                                <i class="fas fa-exclamation-circle me-1"></i>Loss for today
                            @endif
                        </span>
                        <i class="fas fa-balance-scale {{ $todayData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}"></i>
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
                                <td class="fw-bold text-primary py-2">Income (Sales)</td>
                                <td class="text-end py-2 fw-bold">{{ formatMoney($todayData['income']) }}</td>
                            </tr>

                            <!-- Purchases -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-info py-2">Purchases</td>
                                <td class="text-end py-2 fw-bold text-info">{{ formatMoney($todayData['purchases']) }}</td>
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
                                <td class="fw-bold text-primary py-2">Income (Sales)</td>
                                <td class="text-end py-2 fw-bold">{{ formatMoney($monthData['income']) }}</td>
                            </tr>

                            <!-- Purchases -->
                            <tr class="border-bottom">
                                <td class="fw-bold text-info py-2">Purchases</td>
                                <td class="text-end py-2 fw-bold text-info">{{ formatMoney($monthData['purchases']) }}</td>
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

    <!-- Recent Transactions Section with Tabs -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-receipt text-primary me-2"></i>
                        Recent Transactions
                    </h5>
                    <small class="text-muted">Last 5 transactions</small>
                </div>
                <div class="card-body p-0">
                    <ul class="nav nav-tabs px-3 pt-3" id="transactionTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab" aria-controls="sales" aria-selected="true">
                                <i class="fas fa-shopping-cart me-1"></i> Sales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="purchase-tab" data-bs-toggle="tab" data-bs-target="#purchase" type="button" role="tab" aria-controls="purchase" aria-selected="false">
                                <i class="fas fa-shopping-bag me-1"></i> Purchase
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="transactionTabsContent">
                        <!-- Sales Tab -->
                        <div class="tab-pane fade show active" id="sales" role="tabpanel" aria-labelledby="sales-tab">
                            @can('sales-order.view')
                                @if($recentSalesOrders->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-start">Date</th>
                                                    <th>Amount</th>
                                                    <th>Customer</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentSalesOrders as $sale)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bold">{{ $sale->order_date->format('M j, Y') }}</span>
                                                                <small class="text-muted">{{ $sale->order_number }}</small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success fs-6">{{ formatMoney($sale->total_amount) }}</span>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong>{{ $sale->customer->name ?? 'Walk-in' }}</strong>
                                                                @if($sale->customer && $sale->customer->email)
                                                                    <br><small class="text-muted">{{ $sale->customer->email }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('modules.sales-order.show', $sale->id) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3 text-center border-top bg-light">
                                        <a href="{{ route('modules.sales-order.index') }}" class="text-primary text-decoration-none">
                                            View all sales <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                @else
                                    <div class="p-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">No recent sales transactions</p>
                                    </div>
                                @endif
                            @else
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-lock fa-2x mb-3 opacity-25"></i>
                                    <p class="mb-0">You don't have permission to view sales orders</p>
                                </div>
                            @endcan
                        </div>

                        <!-- Purchase Tab -->
                        <div class="tab-pane fade" id="purchase" role="tabpanel" aria-labelledby="purchase-tab">
                            @can('purchase-order.view')
                                @if($recentPurchaseOrders->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-start">Date</th>
                                                    <th>Amount</th>
                                                    <th>Supplier</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($recentPurchaseOrders as $purchase)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex flex-column">
                                                                <span class="fw-bold">{{ $purchase->order_date->format('M j, Y') }}</span>
                                                                <small class="text-muted">{{ $purchase->po_number }}</small>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-warning text-dark fs-6">{{ formatMoney($purchase->total_amount) }}</span>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong>{{ $purchase->supplier->name ?? $purchase->supplier_name }}</strong>
                                                                @if($purchase->supplier && $purchase->supplier->email)
                                                                    <br><small class="text-muted">{{ $purchase->supplier->email }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td class="text-end">
                                                            <a href="{{ route('modules.purchase-order.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="p-3 text-center border-top bg-light">
                                        <a href="{{ route('modules.purchase-order.index') }}" class="text-primary text-decoration-none">
                                            View all purchases <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                @else
                                    <div class="p-5 text-center text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                        <p class="mb-0">No recent purchase transactions</p>
                                    </div>
                                @endif
                            @else
                                <div class="p-5 text-center text-muted">
                                    <i class="fas fa-lock fa-2x mb-3 opacity-25"></i>
                                    <p class="mb-0">You don't have permission to view purchase orders</p>
                                </div>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
