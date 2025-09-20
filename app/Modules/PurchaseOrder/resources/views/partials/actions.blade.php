<div class="btn-group btn-group-sm" role="group">
    @can('purchase-order.edit')
        <a href="{{ route('modules.purchase-order.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('purchase-order.delete')
        <form method="POST" action="{{ route('modules.purchase-order.destroy', $id) }}" onsubmit="return confirm('Delete this purchase_order?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
