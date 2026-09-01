@extends('layouts.app')

@section('title', $category->name . ' Discussions - DiscussHub')

@section('content')
<div class="dg-card p-4 mb-4" style="background: linear-gradient(135deg, {{ $category->color }}08 0%, rgba(255,255,255,1) 100%);">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="p-3 rounded fs-3" style="background: {{ $category->color }}20; color: {{ $category->color }};">
                <i class="{{ $category->icon }}"></i>
            </span>
            <div>
                <h2 class="fw-bold text-dark mb-1">{{ $category->name }}</h2>
                <p class="text-secondary small mb-0">{{ $category->description ?? 'Discussions, questions, and guides relating to ' . $category->name }}</p>
            </div>
        </div>

        @auth
            <a href="{{ route('questions.create') }}" class="btn-primary-dg">
                <i class="bi bi-plus-circle"></i> Ask in {{ $category->name }}
            </a>
        @endauth
    </div>
</div>

<div class="dg-card overflow-hidden">
    @forelse($questions as $question)
        <div class="question-card">
            <div class="vote-box">
                <span class="vote-count">{{ $question->vote_score }}</span>
                <span class="vote-label">likes</span>
                <div class="mt-2">
                    <span class="answer-badge {{ $question->is_answered ? 'solved' : '' }}">
                        @if($question->is_answered) <i class="bi bi-check-circle-fill"></i> @endif
                        {{ $question->answer_count }}
                    </span>
                </div>
            </div>

            <div class="flex-grow-1 min-w-0">
                <h5 class="fw-bold mb-2">
                    <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" class="text-decoration-none text-dark">
                        {{ $question->title }}
                    </a>
                </h5>
                <p class="text-secondary small mb-3 text-truncate">{{ Str::limit(strip_tags($question->description), 160) }}</p>

                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($question->tags as $tag)
                            <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge text-decoration-none">#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                    <div class="small text-muted">
                        Asked {{ $question->created_at->diffForHumans() }} by {{ $question->user->user_name }}
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-5">
            <i class="bi bi-folder2-open text-muted display-4"></i>
            <h5 class="mt-3 text-secondary">No discussions in this category yet</h5>
            <p class="small text-muted mb-3">Be the first to post a question under {{ $category->name }}.</p>
            <a href="{{ route('questions.create') }}" class="btn-primary-dg"><i class="bi bi-plus-circle"></i> Ask Question</a>
        </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $questions->links() }}
</div>
@endsection
