<div class="btn-group btn-group-sm" role="group">
    @can('sales-order-item.edit')
        <a href="{{ route('modules.sales-order-item.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('sales-order-item.delete')
        <form method="POST" action="{{ route('modules.sales-order-item.destroy', $id) }}" onsubmit="return confirm('Delete this sales_order_item?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
