<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('modules.products.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    <form method="POST" action="{{ route('modules.products.destroy', $id) }}" onsubmit="return confirm('Delete this product?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
    </form>
</div>
