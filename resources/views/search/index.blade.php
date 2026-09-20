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
            <form action="{{ route('search') }}" method="GET" class="row g-2 align-items-center dg-realtime-search-form">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control form-control-dg border-start-0 ps-0 dg-realtime-input" placeholder="Search keywords, errors, frameworks..." value="{{ $queryText }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-3 col-md-3">
                    <select name="category" class="form-select form-select-sm form-control-dg">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3 col-md-3">
                    <select name="sort" class="form-select form-select-sm form-control-dg">
                        <option value="relevance" {{ $sort === 'relevance' ? 'selected' : '' }}>Most Relevant</option>
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="votes" {{ $sort === 'votes' ? 'selected' : '' }}>Most Liked</option>
                        <option value="views" {{ $sort === 'views' ? 'selected' : '' }}>Most Viewed</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Realtime Results Container -->
        <div id="dg-realtime-results-container" class="dg-realtime-results-wrapper">
            <div class="d-flex flex-column">
                @forelse($questions as $question)
                    <div class="question-item mb-3" data-href="{{ route('questions.show', [$question->id, $question->slug]) }}">
                        <!-- Row 1: Unified Inline Stats, Category & Special Badges in 1 Row -->
                        <div class="question-header-meta">
                            <!-- Likes Pill -->
                            <span class="stat-pill-inline votes" title="{{ $question->vote_score }} likes">
                                <i class="bi bi-heart-fill text-danger me-1"></i>
                                <span class="stat-inline-val">{{ $question->vote_score }}</span>
                                <span class="stat-inline-txt">likes</span>
                            </span>

                            <!-- Answers Pill -->
                            <span class="stat-pill-inline answers {{ $question->is_answered ? ($question->accepted_answer_id ? 'accepted' : 'answered') : 'unanswered' }}"
                                  title="{{ $question->answer_count }} answers">
                                @if($question->accepted_answer_id)
                                    <i class="bi bi-patch-check-fill me-1"></i>
                                @elseif($question->is_answered)
                                    <i class="bi bi-check2 me-1"></i>
                                @else
                                    <i class="bi bi-chat-left-dots me-1"></i>
                                @endif
                                <span class="stat-inline-val">{{ $question->answer_count }}</span>
                                <span class="stat-inline-txt">{{ Str::plural('answer', $question->answer_count) }}</span>
                            </span>

                            <!-- Category Badge -->
                            <a href="{{ route('categories.show', $question->category->slug) }}" class="category-badge">
                                <i class="bi bi-folder-fill category-icon"></i>
                                <span>{{ $question->category->name }}</span>
                            </a>

                            <!-- AI Synthesized Badge -->
                            @if($question->ai_summary)
                                <span class="badge badge-synthesized" title="AI Synthesized Discussion">
                                    <i class="bi bi-stars me-1 ai-stars-spin"></i> Synthesized
                                </span>
                            @endif

                            <!-- Pinned Badge -->
                            @if($question->is_pinned)
                                <span class="badge badge-pinned">
                                    <i class="bi bi-pin-angle-fill me-1"></i> Pinned
                                </span>
                            @endif
                        </div>

                        <!-- Row 2: Question Title -->
                        <h5 class="fw-bold question-title-heading">
                            <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" class="question-title-link">
                                {{ $question->title }}
                            </a>
                        </h5>

                        <!-- Row 3: Question Description Snippet -->
                        <p class="question-desc-snippet mb-2">
                            {{ Str::limit(strip_tags($question->description), 140) }}
                        </p>

                        <!-- Row 4: Question Footer -->
                        <div class="question-footer-row">
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                @foreach($question->tags as $tag)
                                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                                        <span class="tag-hash">#</span>{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>

                            <div class="d-flex align-items-center gap-2 small text-secondary question-author-meta">
                                <a href="{{ route('users.show', $question->user->id) }}" class="question-author-link">
                                    @if($question->user->profile_image && $question->user->profile_image !== 'default_profile.png')
                                        <img src="{{ asset('profiles/' . $question->user->profile_image) }}" class="rounded-circle object-fit-cover question-author-avatar" width="20" height="20" alt="avatar">
                                    @else
                                        <div class="question-author-initial" style="width: 20px; height: 20px; font-size: 0.65rem;">
                                            {{ strtoupper(substr($question->user->user_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <span class="author-name-text">{{ $question->user->user_name }}</span>
                                </a>
                                <span class="meta-dot">·</span>
                                <span class="meta-time" title="{{ $question->created_at->toDayDateTimeString() }}"><i class="bi bi-clock me-1"></i>{{ $question->created_at->diffForHumans() }}</span>
                                <span class="meta-dot">·</span>
                                <span class="meta-views"><i class="bi bi-eye me-1 meta-eye-icon"></i>{{ $question->view_count }}</span>
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
        </div>
    </div>

    <!-- Right Filter Sidebar -->
    <div class="col-lg-4">
        <div class="dg-sticky-sidebar">
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
</div>

<!-- Full-Width Centered Pagination (Both Left & Right Columns End in the Same Row) -->
<div class="row mt-2">
    <div class="col-12 d-flex justify-content-center">
        {{ $questions->links() }}
    </div>
</div>
@endsection
