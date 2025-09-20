<div class="btn-group btn-group-sm" role="group">
    @can('settings.edit')
        <a href="{{ route('modules.settings.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    @endcan
    @can('settings.delete')
        <form method="POST" action="{{ route('modules.settings.destroy', $id) }}" onsubmit="return confirm('Delete this settings?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
        </form>
    @endcan
</div>
