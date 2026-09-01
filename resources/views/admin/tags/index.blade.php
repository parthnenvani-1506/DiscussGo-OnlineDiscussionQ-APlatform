@extends('layouts.admin')

@section('title', 'Manage Tags & Merging - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Tags Management & Merging</h2>
        <p class="text-secondary small mb-0">{{ $tags->total() }} tags created</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Quick Create Tag -->
    <div class="col-md-6">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-tag-fill text-primary me-2"></i> Create New Tag</h5>
            <form action="{{ route('admin.tags.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tag Name</label>
                    <input type="text" name="name" class="form-control form-control-dg" placeholder="e.g. docker" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description (Optional)</label>
                    <input type="text" name="description" class="form-control form-control-dg" placeholder="Short description...">
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                    <i class="bi bi-plus-lg me-1"></i> Add Tag
                </button>
            </form>
        </div>
    </div>

    <!-- Tag Merging Tool -->
    <div class="col-md-6">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-bezier2 text-warning me-2"></i> Merge Duplicate Tags</h5>
            <p class="text-secondary small mb-3">Reassigns all questions from Source Tag to Target Tag and deletes Source Tag.</p>
            <form action="{{ route('admin.tags.merge') }}" method="POST" onsubmit="return confirm('Are you sure you want to merge these tags? This cannot be undone.');">
                @csrf
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Source (Will be deleted)</label>
                        <select name="source_tag_id" class="form-select form-control-dg" required>
                            <option value="">-- Choose Tag --</option>
                            @foreach($allTags as $t)
                                <option value="{{ $t->id }}">#{{ $t->name }} ({{ $t->usage_count }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-semibold">Target (Will keep)</label>
                        <select name="target_tag_id" class="form-select form-control-dg" required>
                            <option value="">-- Choose Tag --</option>
                            @foreach($allTags as $t)
                                <option value="{{ $t->id }}">#{{ $t->name }} ({{ $t->usage_count }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4">
                    <i class="bi bi-arrow-down-up me-1"></i> Execute Merge
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Tags Table -->
<div class="dg-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Tag Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Usage Count</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tags as $tag)
                    <tr>
                        <td><span class="tag-badge">#{{ $tag->name }}</span></td>
                        <td><code>{{ $tag->slug }}</code></td>
                        <td>{{ $tag->description ?? '-' }}</td>
                        <td><span class="badge bg-light text-secondary border">{{ $tag->usage_count }}</span></td>
                        <td class="text-end">
                            <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete tag">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No tags found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $tags->links() }}
</div>
@endsection
