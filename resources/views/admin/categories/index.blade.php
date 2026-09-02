@extends('layouts.admin')

@section('title', 'Manage Categories - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Category Management</h2>
        <p class="text-secondary small mb-0">Manage discussion classifications and their settings.</p>
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
                    <th class="px-4 py-3">Category</th>
                    <th class="py-3">Slug</th>
                    <th class="py-3">Description</th>
                    <th class="py-3 text-center">Discussions</th>
                    <th class="py-3 text-end px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr>
                        {{-- Category name — plain, no colour dot --}}
                        <td class="px-4">
                            <span class="fw-semibold text-dark">{{ $cat->name }}</span>
                        </td>

                        {{-- Slug --}}
                        <td><code class="small">{{ $cat->slug }}</code></td>

                        {{-- Description excerpt --}}
                        <td class="text-secondary" style="max-width:280px;">
                            {{ $cat->description ? Str::limit($cat->description, 60) : '—' }}
                        </td>

                        {{-- Discussion count --}}
                        <td class="text-center">
                            <span class="badge bg-light text-secondary border">{{ $cat->questions_count }}</span>
                        </td>

                        {{-- Actions --}}
                        <td class="text-end px-4">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.categories.edit', $cat) }}"
                                    class="btn btn-sm btn-outline-secondary" title="Edit category">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Delete category \'{{ $cat->name }}\'? This cannot be undone.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete category"
                                        {{ $cat->questions_count > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-3 d-block mb-2 opacity-50"></i>
                            No categories configured yet.
                            <a href="{{ route('admin.categories.create') }}" class="d-block mt-2 text-primary small">Create the first one</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Summary footer --}}
@if($categories->count() > 0)
    <div class="mt-3 text-secondary small text-end px-1">
        {{ $categories->count() }} {{ Str::plural('category', $categories->count()) }} total
    </div>
@endif

@endsection
