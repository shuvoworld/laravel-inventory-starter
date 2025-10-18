<div class="btn-group btn-group-sm" role="group">
    @can('brand.edit')
        <a href="{{ route('modules.brand.edit', $id) }}" class="btn btn-outline-primary">
            <i class="fas fa-edit"></i>
        </a>
    @endcan
    @can('brand.delete')
        <form method="POST" action="{{ route('modules.brand.destroy', $id) }}"
              onsubmit="return confirm('Are you sure you want to delete this brand?');"
              style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
