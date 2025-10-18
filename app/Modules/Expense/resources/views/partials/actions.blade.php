@can('expense.edit')
    <a href="{{ route('modules.expenses.edit', $id) }}" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-edit"></i>
    </a>
@endcan

@can('expense.delete')
    <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('modules.expenses.destroy', $id) }}', '{{ $id }}')">
        <i class="fas fa-trash"></i>
    </button>
@endcan

<script>
function confirmDelete(url, id) {
    if (confirm('Are you sure you want to delete this expense? This action cannot be undone.')) {
        // Create a form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrfInput);

        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>