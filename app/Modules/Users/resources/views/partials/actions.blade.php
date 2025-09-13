@canany(['users.view','users.edit','users.delete'])
<div class="btn-group btn-group-sm" role="group">
    @can('users.view')
        <a class="btn btn-outline-secondary" href="{{ route('modules.users.show', $user->id) }}" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    @can('users.edit')
        <a class="btn btn-outline-primary" href="{{ route('modules.users.edit', $user->id) }}" title="Edit">
            <i class="fas fa-pen"></i>
        </a>
    @endcan
    @can('users.delete')
        <form method="POST" action="{{ route('modules.users.destroy', $user->id) }}" onsubmit="return confirm('Delete this user?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
@endcanany
