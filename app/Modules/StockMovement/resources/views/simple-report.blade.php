@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Simple Transaction Report</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Movements
        </a>
        <a href="{{ route('modules.stock-movement.simple-report', request()->query()) }}" class="btn btn-primary">
            <i class="fas fa-sync me-1"></i> Refresh
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="fas fa-list fa-2x text-primary mb-2"></i>
                <h5 class="card-title">{{ $summary['total_transactions'] }}</h5>
                <p class="text-muted small mb-0">Total Transactions</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="fas fa-arrow-up fa-2x text-success mb-2"></i>
                <h5 class="card-title">{{ number_format($summary['total_in']) }}</h5>
                <p class="text-muted small mb-0">Total Stock In</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body text-center">
                <i class="fas fa-arrow-down fa-2x text-danger mb-2"></i>
                <h5 class="card-title">{{ number_format($summary['total_out']) }}</h5>
                <p class="text-muted small mb-0">Total Stock Out</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="fas fa-balance-scale fa-2x text-warning mb-2"></i>
                <h5 class="card-title">{{ number_format($summary['total_adjustments']) }}</h5>
                <p class="text-muted small mb-0">Total Adjustments</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-filter me-2"></i>Filters
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('modules.stock-movement.simple-report') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select id="product_id" name="product_id" class="form-select">
                        <option value="">All Products</option>
                        @foreach($products as $id => $name)
                            <option value="{{ $id }}" {{ ($filters['product_id'] ?? '') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="movement_type" class="form-label">Movement Type</label>
                    <select id="movement_type" name="movement_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="in" {{ ($filters['movement_type'] ?? '') == 'in' ? 'selected' : '' }}>Stock In</option>
                        <option value="out" {{ ($filters['movement_type'] ?? '') == 'out' ? 'selected' : '' }}>Stock Out</option>
                        <option value="adjustment" {{ ($filters['movement_type'] ?? '') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                    </select>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('modules.stock-movement.simple-report') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Transaction Table -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-table me-2"></i>Recent Transactions (Last 100)
        </h5>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable-minimal table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Transaction Details</th>
                            <th>User</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transactions as $transaction)
                            <tr>
                                <td><span class="badge bg-secondary">#{{ $transaction->id }}</span></td>
                                <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <strong>{{ $transaction->product->name }}</strong>
                                    @if($transaction->product->sku)
                                        <br><small class="text-muted">SKU: {{ $transaction->product->sku }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $movementType = $transaction->movement_type ?? $transaction->type ?? 'adjustment';
                                        $badges = [
                                            'in' => 'bg-success',
                                            'out' => 'bg-danger',
                                            'adjustment' => 'bg-warning'
                                        ];
                                        $class = $badges[$movementType] ?? 'bg-secondary';
                                        $icons = [
                                            'in' => 'fa-plus-circle',
                                            'out' => 'fa-minus-circle',
                                            'adjustment' => 'fa-balance-scale'
                                        ];
                                        $icon = $icons[$movementType] ?? 'fa-question-circle';
                                    @endphp
                                    <span class="badge {{ $class }}">
                                        <i class="fas {{ $icon }} me-1"></i>{{ ucfirst($movementType) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        if ($movementType === 'in') {
                                            echo '<span class="text-success fw-bold">+' . number_format($transaction->quantity) . '</span>';
                                        } elseif ($movementType === 'out') {
                                            echo '<span class="text-danger fw-bold">-' . number_format($transaction->quantity) . '</span>';
                                        } else {
                                            echo '<span class="text-warning fw-bold">' . number_format($transaction->quantity) . '</span>';
                                        }
                                    @endphp
                                </td>
                                <td>
                                    @php
                                        $allTransactionTypes = \App\Modules\StockMovement\Models\StockMovement::getTransactionTypes();
                                        $transactionLabel = $allTransactionTypes[$transaction->transaction_type] ?? ucfirst($transaction->transaction_type);
                                    @endphp
                                    <span class="badge bg-info text-dark">{{ $transactionLabel }}</span>
                                    @if($transaction->reference_type && $transaction->reference_id)
                                        <br><small class="text-muted">{{ $transaction->reference_type }} #{{ $transaction->reference_id }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->user)
                                        {{ $transaction->user->name }}
                                    @else
                                        <em class="text-muted">System</em>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->notes)
                                        <small>{{ Str::limit($transaction->notes, 50) }}</small>
                                    @else
                                        <em class="text-muted">No notes</em>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No transactions found</h5>
                <p class="text-muted">Try adjusting your filters or check back later for new transactions.</p>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-refresh functionality (optional)
// setInterval(function() {
//     window.location.reload();
// }, 30000); // Refresh every 30 seconds
</script>
@endpush