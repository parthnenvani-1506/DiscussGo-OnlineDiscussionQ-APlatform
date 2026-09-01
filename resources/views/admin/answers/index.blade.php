@extends('layouts.admin')

@section('title', 'Manage Answers - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Answer Management</h2>
        <p class="text-secondary small mb-0">{{ $answers->total() }} answers posted across questions</p>
    </div>
</div>

<div class="dg-card p-3 mb-4">
    <form action="{{ route('admin.answers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-8">
            <input type="text" name="q" class="form-control form-control-dg" placeholder="Search answer text..." value="{{ $search }}">
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select form-control-dg" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="flagged" {{ $status === 'flagged' ? 'selected' : '' }}>🚩 AI Flagged Only</option>
                <option value="accepted" {{ $status === 'accepted' ? 'selected' : '' }}>✓ Accepted Solutions Only</option>
            </select>
        </div>
    </form>
</div>

<div class="dg-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Answer Excerpt</th>
                    <th>Question</th>
                    <th>Author</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($answers as $ans)
                    <tr>
                        <td class="text-truncate" style="max-width: 250px;">
                            {{ Str::limit(strip_tags($ans->answer), 80) }}
                        </td>
                        <td class="text-truncate" style="max-width: 200px;">
                            @if($ans->question)
                                <a href="{{ route('admin.questions.show', $ans->question) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $ans->question->title }}
                                </a>
                            @else
                                <span class="text-muted fst-italic">Question deleted</span>
                            @endif
                        </td>
                        <td>{{ $ans->user->user_name }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $ans->vote_score }} likes</span></td>
                        <td>
                            @if($ans->is_accepted)
                                <span class="badge bg-success">Accepted</span>
                            @elseif($ans->is_flagged)
                                <span class="badge bg-danger">Flagged</span>
                            @else
                                <span class="badge bg-light text-secondary border">Standard</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $ans->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.answers.destroy', $ans) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this answer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete answer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">No answers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $answers->links() }}
</div>
@endsection
