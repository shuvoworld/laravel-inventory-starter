<div class="btn-group btn-group-sm" role="group">
    @can('product-categories.edit')
    <a href="{{ route('modules.product-categories.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('product-categories.delete')
    <form method="POST" action="{{ route('modules.product-categories.destroy', $id) }}" onsubmit="return confirm('Delete this category?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
    </form>
    @endcan
</div>
