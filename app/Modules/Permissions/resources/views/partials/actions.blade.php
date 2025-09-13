@canany(['permissions.view','permissions.edit','permissions.delete'])
<div class="btn-group btn-group-sm" role="group">
    @can('permissions.view')
        <a class="btn btn-outline-secondary" href="{{ route('modules.permissions.show', $permission->id) }}" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    @can('permissions.edit')
        <a class="btn btn-outline-primary" href="{{ route('modules.permissions.edit', $permission->id) }}" title="Edit">
            <i class="fas fa-pen"></i>
        </a>
    @endcan
    @can('permissions.delete')
        <form method="POST" action="{{ route('modules.permissions.destroy', $permission->id) }}" onsubmit="return confirm('Delete this permission?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
@endcanany
