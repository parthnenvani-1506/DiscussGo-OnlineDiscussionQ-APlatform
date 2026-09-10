@extends('layouts.app')

@section('title', $category->name . ' Discussions - DiscussHub')

@section('content')
<div class="dg-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="p-3 rounded fs-3 bg-primary-subtle text-primary">
                <i class="bi bi-folder-fill"></i>
            </span>
            <div>
                <h2 class="fw-bold text-dark mb-1">{{ $category->name }}</h2>
                <p class="text-secondary small mb-0">{{ $category->description ?? 'Discussions, questions, and guides relating to ' . $category->name }}</p>
            </div>
        </div>

        @auth
            <a href="{{ route('questions.create') }}" class="dg-btn-cta px-3 py-2 text-white text-decoration-none">
                <i class="bi bi-plus-circle"></i> Ask in {{ $category->name }}
            </a>
        @endauth
    </div>
</div>

<!-- Category In-Page Search & Sorting Filter -->
<div class="dg-card p-3 mb-4">
    <form action="{{ route('categories.show', $category->slug) }}" method="GET" class="row g-2 align-items-center dg-realtime-search-form">
        <div class="col-md-8">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" 
                       name="q" 
                       class="form-control form-control-dg border-start-0 ps-0 dg-realtime-input" 
                       placeholder="Search inside {{ $category->name }}..." 
                       value="{{ $search }}" 
                       autocomplete="off">
            </div>
        </div>
        <div class="col-md-4">
            <select name="sort" class="form-select form-select-sm form-control-dg">
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Sort: Newest First</option>
                <option value="votes" {{ $sort === 'votes' ? 'selected' : '' }}>Sort: Most Liked</option>
                <option value="unanswered" {{ $sort === 'unanswered' ? 'selected' : '' }}>Sort: Unanswered</option>
            </select>
        </div>
    </form>
</div>

<!-- Realtime Category Questions Results Container -->
<div id="dg-realtime-results-container" class="dg-realtime-results-wrapper">
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
                <h5 class="mt-3 text-secondary">No discussions found</h5>
                <p class="small text-muted mb-3">{{ !empty($search) ? 'No discussions matching "' . $search . '" in this category.' : 'Be the first to post a question under ' . $category->name . '.' }}</p>
                @if(!empty($search))
                    <a href="{{ route('categories.show', $category->slug) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-2">Clear Search</a>
                @endif
                <a href="{{ route('questions.create') }}" class="dg-btn-cta px-3 py-2 text-white text-decoration-none"><i class="bi bi-plus-circle"></i> Ask Question</a>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $questions->links() }}
    </div>
</div>
@endsection
