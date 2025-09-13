<div class="d-flex gap-2">
    @can('types.view')
        <a href="{{ route('modules.types.show', $id) }}" class="btn btn-sm btn-outline-primary">View</a>
    @endcan
    @can('types.edit')
        <a href="{{ route('modules.types.edit', $id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
    @endcan
    @can('types.delete')
        <form method="POST" action="{{ route('modules.types.destroy', $id) }}" onsubmit="return confirm('Delete?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
    @endcan
</div>
