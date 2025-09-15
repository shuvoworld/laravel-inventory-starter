@canany(['users.view','users.edit','users.delete'])
<div class="d-flex gap-2">
    @can('users.view')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('modules.users.show', $user->id) }}">View</a>
    @endcan
    @can('users.edit')
        <a class="btn btn-sm btn-outline-primary" href="{{ route('modules.users.edit', $user->id) }}">Edit</a>
    @endcan
    @can('users.delete')
        <form method="POST" action="{{ route('modules.users.destroy', $user->id) }}" onsubmit="return confirm('Delete this user?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
    @endcan
</div>
@endcanany
