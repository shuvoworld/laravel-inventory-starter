@extends('layouts.module')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Blog Category Details</h3>
        <div class="d-flex gap-2">
            @can('blog-category.edit')
                <a class="btn btn-sm btn-primary" href="{{ route('modules.blog-category.edit', $item->id) }}">
                    <i class="fas fa-pen me-1"></i> Edit
                </a>
            @endcan
            @can('blog-category.delete')
                <form method="POST" action="{{ route('modules.blog-category.destroy', $item->id) }}" onsubmit="return confirm('Delete this blog category?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">ID</dt>
            <dd class="col-sm-9">{{ $item->id }}</dd>

            <dt class="col-sm-3">Name</dt>
            <dd class="col-sm-9">{{ $item->name }}</dd>

            <dt class="col-sm-3">Created</dt>
            <dd class="col-sm-9">{{ $item->created_at }}</dd>

            <dt class="col-sm-3">Updated</dt>
            <dd class="col-sm-9">{{ $item->updated_at }}</dd>
        </dl>
    </div>
    <div class="card-footer">
        <a href="{{ route('modules.blog-category.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection
