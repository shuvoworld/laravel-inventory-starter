<div class="btn-group btn-group-sm" role="group">
    @can('attribute-set.edit')
        <a href="{{ route('modules.attribute-set.edit', $id) }}" class="btn btn-outline-primary">
            <i class="fas fa-edit"></i>
        </a>
    @endcan
    @can('attribute-set.delete')
        <form method="POST" action="{{ route('modules.attribute-set.destroy', $id) }}"
              onsubmit="return confirm('Are you sure you want to delete this attribute set?');"
              style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
