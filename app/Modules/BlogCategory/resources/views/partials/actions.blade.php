@canany(['blog-category.view','blog-category.edit','blog-category.delete'])
<div class="btn-group btn-group-sm" role="group">
    @can('blog-category.view')
        <a class="btn btn-outline-secondary" href="{{ route('modules.blog-category.show', $category->id) }}" title="View">
            <i class="fas fa-eye"></i>
        </a>
    @endcan
    @can('blog-category.edit')
        <a class="btn btn-outline-primary" href="{{ route('modules.blog-category.edit', $category->id) }}" title="Edit">
            <i class="fas fa-pen"></i>
        </a>
    @endcan
    @can('blog-category.delete')
        <form method="POST" action="{{ route('modules.blog-category.destroy', $category->id) }}" onsubmit="return confirm('Delete this category?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger" title="Delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
</div>
@endcanany
