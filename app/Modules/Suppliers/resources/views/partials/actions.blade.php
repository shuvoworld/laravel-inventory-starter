<div class="btn-group" role="group">
    <a href="{{ route('modules.suppliers.show', $supplier->id) }}" class="btn btn-sm btn-info" title="View">
        <i class="fas fa-eye"></i>
    </a>
    <a href="{{ route('modules.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-warning" title="Edit">
        <i class="fas fa-edit"></i>
    </a>
    <button type="button" class="btn btn-sm btn-danger delete-supplier"
            data-url="{{ route('modules.suppliers.destroy', $supplier->id) }}"
            title="Delete">
        <i class="fas fa-trash"></i>
    </button>
</div>