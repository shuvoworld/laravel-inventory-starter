<div class="btn-group btn-group-sm" role="group">
    @can('sales-order.show')
        <a href="{{ route('modules.sales-order.show', $id) }}" class="btn btn-outline-info"><i class="fas fa-eye"></i></a>
    @endcan
    @can('sales-order.edit')
        <a href="{{ route('modules.sales-order.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('sales-order.delete')
        <form method="POST" action="{{ route('modules.sales-order.destroy', $id) }}" onsubmit="return confirm('Delete this sales_order?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
