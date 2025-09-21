<div class="btn-group btn-group-sm" role="group">
    @can('sales-return.show')
        <a href="{{ route('modules.sales-return.show', $id) }}" class="btn btn-outline-info"><i class="fas fa-eye"></i></a>
    @endcan
    @can('sales-return.edit')
        <a href="{{ route('modules.sales-return.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('sales-return.delete')
        <form method="POST" action="{{ route('modules.sales-return.destroy', $id) }}" onsubmit="return confirm('Delete this return?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>