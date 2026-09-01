@extends('layouts.admin')

@section('title', 'Manage Categories - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Category Management</h2>
        <p class="text-secondary small mb-0">Manage discussion classifications, icons, and colors</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
        <i class="bi bi-plus-lg me-1"></i> Add Category
    </a>
</div>

<div class="dg-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Slug</th>
                    <th>Color</th>
                    <th>Discussions</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="p-2 rounded" style="background: {{ $cat->color }}15; color: {{ $cat->color }};">
                                    <i class="{{ $cat->icon }}"></i>
                                </span>
                                <span class="fw-bold text-dark">{{ $cat->name }}</span>
                            </div>
                        </td>
                        <td><code>{{ $cat->slug }}</code></td>
                        <td>
                            <span class="d-inline-flex align-items-center gap-1">
                                <span class="rounded-circle" style="width: 12px; height: 12px; background: {{ $cat->color }};"></span>
                                {{ $cat->color }}
                            </span>
                        </td>
                        <td><span class="badge bg-light text-secondary border">{{ $cat->questions_count }}</span></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" {{ $cat->questions_count > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No categories configured yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
