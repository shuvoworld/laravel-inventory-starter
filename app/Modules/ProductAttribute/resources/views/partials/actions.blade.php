<div class="btn-group btn-group-sm" role="group">
    @can('product-attribute.edit')
        <a href="{{ route('modules.product-attribute.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('product-attribute.delete')
        <form method="POST" action="{{ route('modules.product-attribute.destroy', $id) }}" onsubmit="return confirm('Delete this product_attribute?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
