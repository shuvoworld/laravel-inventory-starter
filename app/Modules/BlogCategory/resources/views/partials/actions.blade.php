@canany(['blog-category.view','blog-category.edit','blog-category.delete'])
<div class="d-flex gap-2">
    @can('blog-category.view')
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('modules.blog-category.show', $category->id) }}">View</a>
    @endcan
    @can('blog-category.edit')
        <a class="btn btn-sm btn-outline-primary" href="{{ route('modules.blog-category.edit', $category->id) }}">Edit</a>
    @endcan
    @can('blog-category.delete')
        <form method="POST" action="{{ route('modules.blog-category.destroy', $category->id) }}" onsubmit="return confirm('Delete this category?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Delete</button>
        </form>
    @endcan
</div>
@endcanany
