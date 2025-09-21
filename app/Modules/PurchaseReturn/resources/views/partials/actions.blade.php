<div class="btn-group btn-group-sm" role="group">
    @can('purchase-return.show')
        <a href="{{ route('modules.purchase-return.show', $id) }}" class="btn btn-outline-info"><i class="fas fa-eye"></i></a>
    @endcan
    @can('purchase-return.edit')
        <a href="{{ route('modules.purchase-return.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('purchase-return.delete')
        <form method="POST" action="{{ route('modules.purchase-return.destroy', $id) }}" onsubmit="return confirm('Delete this return?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>