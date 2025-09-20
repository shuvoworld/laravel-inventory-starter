<div class="btn-group btn-group-sm" role="group">
    @can('purchase-order-item.edit')
        <a href="{{ route('modules.purchase-order-item.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('purchase-order-item.delete')
        <form method="POST" action="{{ route('modules.purchase-order-item.destroy', $id) }}" onsubmit="return confirm('Delete this purchase_order_item?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
