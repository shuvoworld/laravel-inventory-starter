<div class="d-flex gap-2">
    @can('contact.view')
        <a href="{{ route('modules.contact.show', $id) }}" class="btn btn-sm btn-outline-primary">View</a>
    @endcan
    @can('contact.edit')
        <a href="{{ route('modules.contact.edit', $id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
    @endcan
    @can('contact.delete')
        <form method="POST" action="{{ route('modules.contact.destroy', $id) }}" onsubmit="return confirm('Delete?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
    @endcan
</div>
