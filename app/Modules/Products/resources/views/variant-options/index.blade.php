@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Variant Options</h1>
    <a href="{{ route('modules.products.variant-options.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Option
    </a>
</div>

<div class="card">
    <div class="card-body">
        <p class="text-muted mb-3">
            Variant options define the attributes that products can vary by (e.g., Size, Color, Material).
            Create options here, then use them when creating product variants.
        </p>

        @if($options->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No variant options created yet.
                <a href="{{ route('modules.products.variant-options.create') }}">Create your first option</a> to get started.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Order</th>
                            <th>Option Name</th>
                            <th>Values</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($options as $option)
                        <tr>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $option->display_order }}</span>
                            </td>
                            <td>
                                <strong>{{ $option->name }}</strong>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($option->values as $value)
                                        <span class="badge bg-light text-dark border">{{ $value->value }}</span>
                                    @endforeach
                                </div>
                                <small class="text-muted">{{ $option->values_count }} value(s)</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('modules.products.variant-options.edit', $option) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-outline-danger"
                                            onclick="deleteOption({{ $option->id }})"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteOption(optionId) {
    if (!confirm('Are you sure you want to delete this option? This cannot be undone if the option is not in use.')) {
        return;
    }

    fetch(`/modules/products/variant-options/${optionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message || 'Error deleting option');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting option');
    });
}
</script>
@endpush
