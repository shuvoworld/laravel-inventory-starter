@extends('layouts.module')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Module Dictionary</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Namespace</th>
                        <th>Title</th>
                        <th>Path</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modules as $m)
                        <tr>
                            <td>{{ $m->id }}</td>
                            <td><code>{{ $m->name }}</code></td>
                            <td>{{ $m->namespace }}</td>
                            <td>{{ $m->title }}</td>
                            <td class="small text-muted" style="max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $m->path }}">{{ $m->path }}</td>
                            <td>
                                @if($m->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Active</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.modules.toggle', $m) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm {{ $m->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}">
                                        {{ $m->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No modules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="text-muted small">This table is auto-synced from the app/Modules directory on boot.</div>
    </div>
</div>
@endsection
