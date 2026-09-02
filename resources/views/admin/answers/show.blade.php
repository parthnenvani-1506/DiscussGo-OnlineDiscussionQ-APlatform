@extends('layouts.admin')

@section('title', 'Review Answer - DiscussHub Admin')

@section('content')

{{-- ── Page Header ──────────────────────────────────────────── --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Answer Review</h2>
        <p class="text-secondary small mb-0">
            Answer #{{ $answer->id }} · Posted by
            <a href="{{ route('admin.users.show', $answer->user) }}" class="text-primary fw-semibold text-decoration-none">
                {{ $answer->user->user_name }}
            </a>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('questions.show', [$answer->question->id, $answer->question->slug]) }}#answer-{{ $answer->id }}"
            target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-box-arrow-up-right me-1"></i> View on Site
        </a>
        <a href="{{ route('admin.answers.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Answers
        </a>
    </div>
</div>

{{-- ── Status Badges Row ─────────────────────────────────────── --}}
<div class="d-flex align-items-center gap-2 mb-4 flex-wrap">
    @if($answer->is_accepted)
        <span class="badge bg-success fs-6 px-3 py-2">
            <i class="bi bi-check-circle-fill me-1"></i> Accepted Solution
        </span>
    @endif
    @if($answer->is_flagged)
        <span class="badge bg-danger fs-6 px-3 py-2">
            <i class="bi bi-flag-fill me-1"></i> AI Moderation Flagged
        </span>
    @endif
    @if(!$answer->is_accepted && !$answer->is_flagged)
        <span class="badge bg-secondary fs-6 px-3 py-2">
            <i class="bi bi-chat-dots me-1"></i> Standard Answer
        </span>
    @endif
    <span class="badge bg-light text-dark border fs-6 px-3 py-2">
        <i class="bi bi-heart-fill text-danger me-1"></i> {{ $answer->vote_score }} likes
    </span>
    <span class="badge bg-light text-dark border fs-6 px-3 py-2">
        <i class="bi bi-clock me-1"></i> {{ $answer->created_at->format('M d, Y H:i') }}
    </span>
</div>

<div class="row g-4">
    <div class="col-lg-8">

        {{-- ── Answer Content ───────────────────────────────── --}}
        <div class="dg-card p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-chat-left-text text-primary me-2"></i> Answer Content
                </h5>
                <form action="{{ route('admin.answers.destroy', $answer) }}" method="POST"
                    onsubmit="return confirm('Permanently delete this answer? This cannot be undone.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3">
                        <i class="bi bi-trash me-1"></i> Delete Answer
                    </button>
                </form>
            </div>
            <div class="p-3 rounded bg-light border" style="white-space: pre-wrap; line-height: 1.75; font-size: 0.9375rem;">{{ strip_tags($answer->answer) }}</div>
        </div>

        {{-- ── Parent Question ──────────────────────────────── --}}
        <div class="dg-card p-4">
            <h5 class="fw-bold text-dark mb-3">
                <i class="bi bi-patch-question text-primary me-2"></i> Parent Question
            </h5>

            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="badge bg-primary-subtle text-primary border border-primary">
                    <i class="bi bi-folder me-1"></i>
                    {{ $answer->question->category->name }}
                </span>
                @if($answer->question->is_answered)
                    <span class="badge bg-success-subtle text-success border border-success">
                        <i class="bi bi-check-circle-fill me-1"></i> Solved
                    </span>
                @endif
                @if($answer->question->is_pinned)
                    <span class="badge bg-warning-subtle text-warning border border-warning">
                        <i class="bi bi-pin-angle-fill me-1"></i> Pinned
                    </span>
                @endif
                @if($answer->question->is_flagged)
                    <span class="badge bg-danger">
                        <i class="bi bi-flag-fill me-1"></i> Flagged
                    </span>
                @endif
            </div>

            <h6 class="fw-bold text-dark mb-2">
                <a href="{{ route('admin.questions.show', $answer->question) }}" class="text-dark text-decoration-none">
                    {{ $answer->question->title }}
                </a>
            </h6>

            @if($answer->question->description)
                <p class="text-secondary small mb-3">{{ Str::limit(strip_tags($answer->question->description), 200) }}</p>
            @endif

            <div class="d-flex align-items-center gap-3 small text-muted flex-wrap">
                <span><i class="bi bi-person me-1"></i> Asked by
                    <a href="{{ route('admin.users.show', $answer->question->user) }}" class="text-primary text-decoration-none fw-semibold">
                        {{ $answer->question->user->user_name }}
                    </a>
                </span>
                <span><i class="bi bi-heart-fill text-danger me-1"></i> {{ $answer->question->vote_score }} likes</span>
                <span><i class="bi bi-eye me-1"></i> {{ $answer->question->view_count }} views</span>
                <span><i class="bi bi-chat-dots me-1"></i> {{ $answer->question->answer_count }} answers</span>
                <span><i class="bi bi-clock me-1"></i> {{ $answer->question->created_at->format('M d, Y') }}</span>
            </div>

            @if($answer->question->tags->count())
                <div class="d-flex flex-wrap gap-1 mt-3">
                    @foreach($answer->question->tags as $tag)
                        <span class="tag-badge">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif

            <div class="mt-3">
                <a href="{{ route('admin.questions.show', $answer->question) }}"
                    class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-right me-1"></i> View Full Question in Admin
                </a>
            </div>
        </div>

    </div>

    {{-- ── Right Sidebar ─────────────────────────────────────── --}}
    <div class="col-lg-4">

        {{-- Author Card --}}
        <div class="dg-card p-4 mb-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-circle text-primary me-1"></i> Author</h6>
            <div class="d-flex align-items-center gap-3 mb-3">
                @if($answer->user->profile_image && $answer->user->profile_image !== 'default_profile.png')
                    <img src="{{ asset('profiles/' . $answer->user->profile_image) }}"
                        class="rounded-circle object-fit-cover" width="48" height="48" alt="avatar">
                @else
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                        style="width: 48px; height: 48px; font-size: 1.1rem;">
                        {{ strtoupper(substr($answer->user->user_name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="fw-bold text-dark">{{ $answer->user->user_name }}</div>
                    <div class="small text-muted">{{ ucfirst($answer->user->level ?? 'newcomer') }}</div>
                </div>
            </div>
            <div class="d-flex flex-column gap-2 small text-secondary mb-3">
                <div class="d-flex justify-content-between">
                    <span>Reputation</span>
                    <strong class="text-dark">{{ number_format($answer->user->reputation) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Total Answers</span>
                    <strong class="text-dark">{{ $answer->user->answers()->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Accepted Answers</span>
                    <strong class="text-dark">{{ $answer->user->answers()->where('is_accepted', true)->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Member Since</span>
                    <strong class="text-dark">{{ $answer->user->created_at->format('M Y') }}</strong>
                </div>
            </div>
            <a href="{{ route('admin.users.show', $answer->user) }}"
                class="btn btn-outline-secondary btn-sm rounded-pill w-100">
                <i class="bi bi-person me-1"></i> View User Profile
            </a>
        </div>

        {{-- Answer Meta --}}
        <div class="dg-card p-4">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-1"></i> Answer Details</h6>
            <div class="d-flex flex-column gap-2 small text-secondary">
                <div class="d-flex justify-content-between">
                    <span>Answer ID</span>
                    <strong class="text-dark">#{{ $answer->id }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Posted</span>
                    <strong class="text-dark">{{ $answer->created_at->diffForHumans() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Last Updated</span>
                    <strong class="text-dark">{{ $answer->updated_at->diffForHumans() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Likes</span>
                    <strong class="text-dark"><i class="bi bi-heart-fill text-danger me-1"></i>{{ $answer->vote_score }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Accepted</span>
                    <strong class="{{ $answer->is_accepted ? 'text-success' : 'text-muted' }}">
                        {{ $answer->is_accepted ? 'Yes' : 'No' }}
                    </strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>AI Flagged</span>
                    <strong class="{{ $answer->is_flagged ? 'text-danger' : 'text-muted' }}">
                        {{ $answer->is_flagged ? 'Yes' : 'No' }}
                    </strong>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
