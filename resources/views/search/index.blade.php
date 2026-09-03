@extends('layouts.app')

@section('title', 'Search Results - DiscussHub')

@section('content')
<div class="row g-4">
    <!-- Main Search Results Column -->
    <div class="col-lg-8">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-search text-primary me-2"></i> Search Results
            </h2>
            @if(!empty($queryText))
                <p class="text-secondary small mb-0">Showing {{ $questions->total() }} discussions matching <strong class="text-dark">"{{ $queryText }}"</strong></p>
            @else
                <p class="text-secondary small mb-0">Showing {{ $questions->total() }} filtered discussions</p>
            @endif
        </div>

        <!-- Search Input with Live Filters -->
        <div class="dg-card p-3 mb-4">
            <form action="{{ route('search') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control form-control-dg" placeholder="Search keywords, errors, frameworks..." value="{{ $queryText }}">
                </div>
                <div class="col-sm-3 col-md-3">
                    <select name="category" class="form-select form-select-sm form-control-dg" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 col-md-3">
                    <select name="sort" class="form-select form-select-sm form-control-dg" onchange="this.form.submit()">
                        <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Most Relevant</option>
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="votes" {{ $sort === 'votes' ? 'selected' : '' }}>Most Liked</option>
                        <option value="views" {{ $sort === 'views' ? 'selected' : '' }}>Most Viewed</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Results Card List -->
        <div class="d-flex flex-column">
            @forelse($questions as $question)
                <div class="question-item">
                    <div class="question-stats-sidebar">
                        <div class="stat-pill votes">
                            <span class="fw-bold"><i class="bi bi-heart-fill text-danger" style="font-size:0.75rem;"></i> {{ $question->vote_score }}</span>
                            <span class="text-muted" style="font-size: 0.65rem;">likes</span>
                        </div>
                        <div class="stat-pill answers {{ $question->is_answered ? ($question->accepted_answer_id ? 'accepted' : 'answered') : '' }}">
                            <span class="fw-bold">
                                @if($question->accepted_answer_id) <i class="bi bi-check-lg"></i> @endif
                                {{ $question->answer_count }}
                            </span>
                            <span style="font-size: 0.65rem;">{{ Str::plural('answer', $question->answer_count) }}</span>
                        </div>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <a href="{{ route('categories.show', $question->category->slug) }}" class="category-badge">
                                <i class="bi bi-folder text-primary"></i> {{ $question->category->name }}
                            </a>
                        </div>

                        <h5 class="fw-bold mb-2">
                            <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" class="text-decoration-none text-dark">
                                {{ $question->title }}
                            </a>
                        </h5>

                        <p class="text-secondary small mb-3 text-truncate">{{ Str::limit(strip_tags($question->description), 160) }}</p>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($question->tags as $tag)
                                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">#{{ $tag->name }}</a>
                                @endforeach
                            </div>
                            <div class="small text-secondary">
                                Asked {{ $question->created_at->diffForHumans() }} by {{ $question->user->user_name }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="dg-card p-5 text-center">
                    <i class="bi bi-search text-muted display-4 mb-3 d-block"></i>
                    <h5 class="text-secondary fw-bold">No discussions found matching your search</h5>
                    <p class="small text-muted mb-4">Try different search terms or ask a new question about this topic.</p>
                    @auth
                        <a href="{{ route('questions.create') }}" class="dg-btn-cta px-4 text-white text-decoration-none"><i class="bi bi-plus-circle me-1"></i> Ask this Question</a>
                    @endauth
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $questions->links() }}
        </div>
    </div>

    <!-- Right Filter Sidebar -->
    <div class="col-lg-4">
        <!-- Categories Filter -->
        <div class="dg-card p-3 mb-4">
            <h6 class="fw-bold mb-3 text-dark">Filter by Category</h6>
            <div class="d-flex flex-column gap-1">
                <a href="{{ route('search', array_merge(request()->query(), ['category' => ''])) }}" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none {{ empty($categoryId) ? 'bg-primary text-white' : 'text-secondary' }}">
                    <span class="small fw-medium">All Categories</span>
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('search', array_merge(request()->query(), ['category' => $cat->id])) }}" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none {{ $categoryId == $cat->id ? 'bg-primary text-white' : 'text-secondary' }}">
                        <span class="small fw-medium">{{ $cat->name }}</span>
                        <span class="badge {{ $categoryId == $cat->id ? 'bg-white text-primary' : 'bg-light text-secondary border' }}">{{ $cat->questions_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Popular Tags Filter -->
        <div class="dg-card p-3">
            <h6 class="fw-bold mb-3 text-dark">Popular Tags</h6>
            <div class="d-flex flex-wrap gap-1">
                @foreach($popularTags as $t)
                    <a href="{{ route('search', array_merge(request()->query(), ['tag' => $t->slug])) }}" class="tag-badge {{ $tagSlug === $t->slug ? 'bg-primary text-white' : '' }}">
                        #{{ $t->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
