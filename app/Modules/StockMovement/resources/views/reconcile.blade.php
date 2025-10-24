@extends('layouts.module')

@section('title', 'Stock Reconciliation')
@section('page-title', 'Stock Reconciliation')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">
            <i class="fas fa-balance-scale me-2"></i>
            Stock Reconciliation
        </h3>
        <a href="{{ route('modules.stock-movement.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Stock Movements
        </a>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Stock Reconciliation</strong> helps you identify and resolve discrepancies between system stock (product.quantity_on_hand) and calculated stock from movements.
            This ensures data integrity and accuracy across your inventory.
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(count($discrepancies) > 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>{{ count($discrepancies) }} discrepancies found!</strong> Please review and reconcile the items below.
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">System Stock</th>
                        <th class="text-center">Movement Stock</th>
                        <th class="text-center">Difference</th>
                        <th class="text-center">Recent Movements</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($discrepancies as $discrepancy)
                    <tr>
                        <td>
                            <strong>{{ $discrepancy['product']->name }}</strong><br>
                            <small class="text-muted">SKU: {{ $discrepancy['product']->sku ?? 'N/A' }}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary-subtle px-3 py-2">
                                {{ $discrepancy['system_stock'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary-subtle px-3 py-2">
                                {{ $discrepancy['movement_stock'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($discrepancy['difference'] > 0)
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger-subtle px-3 py-2">
                                    <i class="fas fa-arrow-up me-1"></i>+{{ $discrepancy['difference'] }}
                                </span>
                            @elseif($discrepancy['difference'] < 0)
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning-subtle px-3 py-2">
                                    <i class="fas fa-arrow-down me-1"></i>{{ $discrepancy['difference'] }}
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-25 text-success border border-success-subtle px-3 py-2">
                                    <i class="fas fa-check me-1"></i>0
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#movementsModal-{{ $discrepancy['product']->id }}">
                                <i class="fas fa-history me-1"></i> View ({{ $discrepancy['movements']->count() }})
                            </button>

                            <!-- Recent Movements Modal -->
                            <div class="modal fade" id="movementsModal-{{ $discrepancy['product']->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-history me-2"></i>
                                                Recent Movements: {{ $discrepancy['product']->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            @if($discrepancy['movements']->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Transaction Type</th>
                                                            <th>Movement Type</th>
                                                            <th class="text-end">Quantity</th>
                                                            <th>User</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($discrepancy['movements'] as $movement)
                                                        <tr>
                                                            <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                                                            <td><span class="badge bg-secondary">{{ $movement->transaction_type }}</span></td>
                                                            <td>
                                                                @if($movement->movement_type === 'in')
                                                                    <span class="badge bg-success">Stock IN</span>
                                                                @elseif($movement->movement_type === 'out')
                                                                    <span class="badge bg-danger">Stock OUT</span>
                                                                @else
                                                                    <span class="badge bg-warning">Adjustment</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-end">
                                                                @if($movement->movement_type === 'in')
                                                                    <span class="text-success fw-bold">+{{ $movement->quantity }}</span>
                                                                @elseif($movement->movement_type === 'out')
                                                                    <span class="text-danger fw-bold">-{{ $movement->quantity }}</span>
                                                                @else
                                                                    <span class="text-warning fw-bold">{{ $movement->quantity }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $movement->user->name ?? 'System' }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @else
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-info-circle me-2"></i>
                                                No movements found for this product.
                                            </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reconcileModal-{{ $discrepancy['product']->id }}">
                                <i class="fas fa-balance-scale me-1"></i> Reconcile
                            </button>

                            <!-- Reconciliation Modal -->
                            <div class="modal fade" id="reconcileModal-{{ $discrepancy['product']->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="fas fa-balance-scale me-2"></i>
                                                Reconcile: {{ $discrepancy['product']->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form method="POST" action="{{ route('modules.stock-movement.reconcile.process') }}">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $discrepancy['product']->id }}">
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <strong>Discrepancy Summary:</strong><br>
                                                    System Stock: <strong>{{ $discrepancy['system_stock'] }}</strong><br>
                                                    Movement Stock: <strong>{{ $discrepancy['movement_stock'] }}</strong><br>
                                                    Difference: <strong>{{ $discrepancy['difference'] }}</strong>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="actual_count_{{ $discrepancy['product']->id }}" class="form-label">
                                                        Actual Physical Count *
                                                    </label>
                                                    <input type="number"
                                                           class="form-control @error('actual_count') is-invalid @enderror"
                                                           id="actual_count_{{ $discrepancy['product']->id }}"
                                                           name="actual_count"
                                                           value="{{ old('actual_count', $discrepancy['movement_stock']) }}"
                                                           min="0"
                                                           required>
                                                    @error('actual_count')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="text-muted">Enter the actual physical count from your inventory check</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="notes_{{ $discrepancy['product']->id }}" class="form-label">
                                                        Notes (Optional)
                                                    </label>
                                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                                              id="notes_{{ $discrepancy['product']->id }}"
                                                              name="notes"
                                                              rows="3"
                                                              placeholder="Enter reason for discrepancy or additional notes...">{{ old('notes') }}</textarea>
                                                    @error('notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-check me-1"></i> Apply Reconciliation
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>All Stock Reconciled!</strong> No discrepancies found. All product quantities match their movement records.
        </div>
        @endif
    </div>

    @if(count($discrepancies) > 0)
    <div class="card-footer">
        <div class="text-muted">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Tip:</strong> Regular reconciliation ensures data accuracy. Consider scheduling physical stock counts monthly or quarterly.
        </div>
    </div>
    @endif
</div>
@endsection