<div class="btn-group btn-group-sm" role="group">
    @can('reports.edit')
        <a href="{{ route('modules.reports.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('reports.delete')
        <form method="POST" action="{{ route('modules.reports.destroy', $id) }}" onsubmit="return confirm('Delete this reports?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
