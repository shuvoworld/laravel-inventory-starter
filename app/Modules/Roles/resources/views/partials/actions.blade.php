@canany(['roles.view','roles.edit','roles.delete'])
<div class="btn-group btn-group-sm" role="group">
    @can('roles.view')
        <a class="btn btn-outline-secondary" href="{{ route('modules.roles.show', $role->id) }}" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    @can('roles.edit')
        <a class="btn btn-outline-primary" href="{{ route('modules.roles.edit', $role->id) }}" title="Edit">
            <i class="fas fa-pen"></i>
        </a>
    @endcan
    @can('roles.delete')
        <form method="POST" action="{{ route('modules.roles.destroy', $role->id) }}" onsubmit="return confirm('Delete this role?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
@endcanany
