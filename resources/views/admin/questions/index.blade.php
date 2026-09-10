@extends('layouts.admin')

@section('title', 'Manage Questions - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Question Management</h2>
        <p class="text-secondary small mb-0">{{ $questions->total() }} discussions on the platform</p>
    </div>
</div>

<div class="dg-card p-3 mb-4">
    <form action="{{ route('admin.questions.index') }}" method="GET" class="row g-2 align-items-center dg-realtime-search-form">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control form-control-dg border-start-0 ps-0 dg-realtime-input" placeholder="Search questions..." value="{{ $search }}" autocomplete="off">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category" class="form-select form-control-dg">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-control-dg">
                <option value="">All Statuses</option>
                <option value="flagged" {{ $status === 'flagged' ? 'selected' : '' }}>🚩 AI Flagged</option>
                <option value="pinned" {{ $status === 'pinned' ? 'selected' : '' }}>📌 Pinned Only</option>
            </select>
        </div>
    </form>
</div>

<!-- Realtime Questions Results -->
<div id="dg-realtime-results-container" class="dg-realtime-results-wrapper">
    <div class="dg-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Score / Answers</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($questions as $q)
                        <tr>
                            <td>
                                <a href="{{ route('admin.questions.show', $q) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ Str::limit($q->title, 55) }}
                                </a>
                            </td>
                            <td>{{ $q->user->user_name }}</td>
                            <td><span class="badge bg-light text-secondary border">{{ $q->category->name }}</span></td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $q->vote_score }} likes</span>
                                <span class="badge bg-light text-dark border">{{ $q->answer_count }} answers</span>
                            </td>
                            <td>
                                @if($q->is_pinned)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-pin-angle-fill"></i> Pinned</span>
                                @endif
                                @if($q->is_flagged)
                                    <span class="badge bg-danger">🚩 Flagged</span>
                                @endif
                                @if(!$q->is_pinned && !$q->is_flagged)
                                    <span class="badge bg-success-subtle text-success border border-success">Normal</span>
                                @endif
                            </td>
                            <td>{{ $q->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <a href="{{ route('admin.questions.show', $q) }}" class="btn btn-sm btn-outline-primary" title="View details">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <form action="{{ route('admin.questions.pin', $q) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $q->is_pinned ? 'btn-warning' : 'btn-outline-secondary' }}" title="Toggle Pin">
                                            <i class="bi bi-pin-angle"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.questions.destroy', $q) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this question and all its answers?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete question">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No questions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $questions->links() }}
    </div>
</div>
@endsection
