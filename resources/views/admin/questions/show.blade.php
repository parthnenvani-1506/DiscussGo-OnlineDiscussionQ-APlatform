@extends('layouts.admin')

@section('title', 'Review Question - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Question Review</h2>
        <p class="text-secondary small mb-0">ID #{{ $question->id }} · Asked by {{ $question->user->user_name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> View on Site
        </a>
        <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="dg-card p-4 mb-4">
    <div class="d-flex align-items-center gap-2 mb-2">
        <span class="badge bg-primary-subtle text-primary border border-primary">
            {{ $question->category->name }}
        </span>
        @if($question->is_pinned)
            <span class="badge bg-warning text-dark"><i class="bi bi-pin-angle-fill"></i> Pinned</span>
        @endif
        @if($question->is_flagged)
            <span class="badge bg-danger">AI Moderation Flagged</span>
        @endif
    </div>

    <h3 class="fw-bold text-dark mb-3">{{ $question->title }}</h3>

    <div class="p-3 rounded bg-light border mb-4">
        {!! nl2br(e($question->description)) !!}
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap mb-4">
        @foreach($question->tags as $tag)
            <span class="tag-badge">#{{ $tag->name }}</span>
        @endforeach
    </div>

    <div class="d-flex align-items-center justify-content-between border-top pt-3">
        <div class="small text-muted">
            <span>Asked {{ $question->created_at->format('M d, Y H:i') }}</span> ·
            <span>{{ $question->vote_score }} likes</span> ·
            <span>{{ $question->view_count }} views</span>
        </div>

        <form action="{{ route('admin.questions.destroy', $question) }}" method="POST" onsubmit="return confirm('Permanently delete this question?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="bi bi-trash me-1"></i> Delete Question
            </button>
        </form>
    </div>
</div>

<div class="dg-card p-4">
    <h5 class="fw-bold text-dark mb-3">Answers ({{ $question->answers->count() }})</h5>
    @forelse($question->answers as $ans)
        <div class="p-3 rounded bg-light border mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold small">{{ $ans->user->user_name }}</span>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary">{{ $ans->vote_score }} likes</span>
                    @if($ans->is_accepted)
                        <span class="badge bg-success">Accepted Solution</span>
                    @endif
                    <form action="{{ route('admin.answers.destroy', $ans) }}" method="POST" onsubmit="return confirm('Delete this answer?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
            <div class="small text-secondary">{!! nl2br(e($ans->answer)) !!}</div>
        </div>
    @empty
        <p class="text-muted small mb-0">No answers posted for this question yet.</p>
    @endforelse
</div>
@endsection
