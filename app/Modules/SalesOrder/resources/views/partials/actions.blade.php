<div class="btn-group btn-group-sm" role="group">
    @can('sales-order.show')
        <a href="{{ route('modules.sales-order.show', $id) }}" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
    @endcan
    @can('sales-order.edit')
        <a href="{{ route('modules.sales-order.edit', $id) }}" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
    @endcan
    @can('sales-order.view')
        <a href="{{ route('modules.sales-order.invoice', $id) }}" class="btn btn-outline-success" title="Print Invoice" target="_blank"><i class="fas fa-print"></i></a>
    @endcan
    @can('sales-order.view')
        <a href="{{ route('modules.sales-order.pos-print', $id) }}" class="btn btn-outline-warning" title="POS Print" target="_blank"><i class="fas fa-receipt"></i></a>
    @endcan
    @can('sales-order.edit')
        @php
            $order = \App\Modules\SalesOrder\Models\SalesOrder::find($id);
        @endphp
        @if($order && $order->status === 'on_hold')
            <form method="POST" action="{{ route('modules.sales-order.release', $id) }}" style="display: inline;" title="Release Order">
                @csrf
                <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i></button>
            </form>
        @else
            <button type="button" class="btn btn-outline-warning" title="Place on Hold" data-bs-toggle="modal" data-bs-target="#holdOrderModal{{ $id }}"><i class="fas fa-pause"></i></button>
        @endif
    @endcan
    @can('sales-order.delete')
        <form method="POST" action="{{ route('modules.sales-order.destroy', $id) }}" onsubmit="return confirm('Delete this sales_order?')" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>

@can('sales-order.edit')
    <!-- Hold Order Modal -->
    <div class="modal fade" id="holdOrderModal{{ $id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Order on Hold</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('modules.sales-order.hold', $id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="hold_reason{{ $id }}" class="form-label">Hold Reason *</label>
                            <textarea id="hold_reason{{ $id }}" name="hold_reason" class="form-control" rows="3" required placeholder="Please provide a reason for placing this order on hold..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Place on Hold</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endcan
