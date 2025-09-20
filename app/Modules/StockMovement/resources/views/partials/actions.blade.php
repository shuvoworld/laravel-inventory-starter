<div class="btn-group btn-group-sm" role="group">
    @can('stock-movement.edit')
        <a href="{{ route('modules.stock-movement.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('stock-movement.delete')
        <form method="POST" action="{{ route('modules.stock-movement.destroy', $id) }}" onsubmit="return confirm('Delete this stock_movement?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
