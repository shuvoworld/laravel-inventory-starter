<div class="btn-group btn-group-sm" role="group">
    <a href="{{ route('modules.suppliers.edit', $id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
    <form method="POST" action="{{ route('modules.suppliers.destroy', $id) }}" onsubmit="return confirm('Delete this supplier?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-outline-danger"><i class="fas fa-trash"></i></button>
    </form>
</div>