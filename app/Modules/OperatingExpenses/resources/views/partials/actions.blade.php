<div class="btn-group btn-group-sm" role="group">
    @can('operating-expenses.view')
        <a href="{{ route('modules.operating-expenses.show', $id) }}" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
    @endcan
    @can('operating-expenses.edit')
        <a href="{{ route('modules.operating-expenses.edit', $id) }}" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
    @endcan
    @can('operating-expenses.delete')
        <form method="POST" action="{{ route('modules.operating-expenses.destroy', $id) }}" onsubmit="return confirm('Delete this expense?')" style="display: inline;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>