@extends('layouts.app')

@section('title', 'Explore Technical Questions - DiscussHub')

@section('content')
<div class="row g-4">
    <!-- Main Content Area -->
    <div class="col-lg-8">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Explore Discussions</h2>
                <p class="text-secondary small mb-0">{{ $questions->total() }} technical discussions across all categories</p>
            </div>
            @auth
                <a href="{{ route('questions.create') }}" class="btn btn-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1 flex-shrink-0">
                    <i class="bi bi-plus-circle"></i> Ask Question
                </a>
            @endauth
        </div>

        <!-- Filters & Sorting Bar -->
        <div class="dg-card p-3 mb-4">
            <form action="{{ route('questions.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Status filter -->
                <div class="col-sm-4">
                    <select name="status" class="form-select form-select-sm form-control-dg" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="answered" {{ request('status') === 'answered' ? 'selected' : '' }}>Solved / Answered</option>
                        <option value="unanswered" {{ request('status') === 'unanswered' ? 'selected' : '' }}>Unanswered</option>
                    </select>
                </div>

                <!-- Category filter -->
                <div class="col-sm-4">
                    <select name="category" class="form-select form-select-sm form-control-dg" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>
                                {{ $category->name }} ({{ $category->questions_count }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sort filter -->
                <div class="col-sm-4">
                    <select name="sort" class="form-select form-select-sm form-control-dg" onchange="this.form.submit()">
                        <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Sort: Newest First</option>
                        <option value="votes" {{ $sort === 'votes' ? 'selected' : '' }}>Sort: Most Liked</option>
                        <option value="answers" {{ $sort === 'answers' ? 'selected' : '' }}>Sort: Most Answered</option>
                        <option value="views" {{ $sort === 'views' ? 'selected' : '' }}>Sort: Most Viewed</option>
                        <option value="oldest" {{ $sort === 'oldest' ? 'selected' : '' }}>Sort: Oldest</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Questions Listing Container -->
        <div class="d-flex flex-column">
            @forelse($questions as $question)
                <div class="question-item">
                    <!-- Left Stats Sidebar -->
                    <div class="question-stats-sidebar">
                        <div class="stat-pill votes">
                            <span class="fw-bold"><i class="bi bi-heart-fill text-danger" style="font-size:0.75rem;"></i> {{ $question->vote_score }}</span>
                            <span class="text-muted" style="font-size: 0.65rem;">likes</span>
                        </div>

                        <div class="stat-pill answers {{ $question->is_answered ? ($question->accepted_answer_id ? 'accepted' : 'answered') : '' }}">
                            <span class="fw-bold">
                                @if($question->accepted_answer_id)
                                    <i class="bi bi-check-lg"></i>
                                @endif
                                {{ $question->answer_count }}
                            </span>
                            <span style="font-size: 0.65rem;">{{ Str::plural('answer', $question->answer_count) }}</span>
                        </div>
                    </div>

                    <!-- Right Question Details -->
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                            <a href="{{ route('categories.show', $question->category->slug) }}" class="category-badge">
                                <i class="bi bi-folder text-primary"></i> {{ $question->category->name }}
                            </a>
                            @if($question->is_pinned)
                                <span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-pin-angle-fill"></i> Pinned</span>
                            @endif
                        </div>

                        <h5 class="fw-bold mb-2">
                            <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" class="text-decoration-none text-dark">
                                {{ $question->title }}
                            </a>
                        </h5>

                        <p class="text-secondary small mb-3 text-truncate" style="max-height: 2.8em;">
                            {{ Str::limit(strip_tags($question->description), 160) }}
                        </p>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($question->tags as $tag)
                                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>

                            <div class="d-flex align-items-center gap-2 small text-secondary">
                                <a href="{{ route('users.show', $question->user->id) }}" class="text-decoration-none text-dark d-flex align-items-center gap-1 fw-medium">
                                    @if($question->user->profile_image && $question->user->profile_image !== 'default_profile.png')
                                        <img src="{{ asset('profiles/' . $question->user->profile_image) }}" class="rounded-circle object-fit-cover" width="20" height="20" alt="avatar">
                                    @else
                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 0.65rem;">
                                            {{ strtoupper(substr($question->user->user_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    {{ $question->user->user_name }}
                                </a>
                                <span>·</span>
                                <span>{{ $question->created_at->diffForHumans() }}</span>
                                <span>·</span>
                                <span><i class="bi bi-eye"></i> {{ $question->view_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="dg-card p-5 text-center">
                    <i class="bi bi-search text-muted display-4 mb-3 d-block"></i>
                    <h5 class="text-secondary fw-bold">No discussions matching your criteria</h5>
                    <p class="small text-muted mb-4">Try adjusting your filters or search keywords.</p>
                    <a href="{{ route('questions.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Reset Filters</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $questions->links() }}
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Categories Sidebar -->
        <div class="dg-card p-3 mb-4">
            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-grid-fill text-primary me-2"></i> Categories</h6>
            <div class="d-flex flex-column gap-1">
                @foreach($categories as $cat)
                    <a href="{{ route('categories.show', $cat->slug) }}" class="d-flex align-items-center justify-content-between p-2 rounded text-decoration-none {{ request('category') === $cat->slug ? 'bg-primary text-white' : 'text-secondary' }}">
                        <span class="small fw-medium"><i class="bi bi-folder me-2"></i> {{ $cat->name }}</span>
                        <span class="badge {{ request('category') === $cat->slug ? 'bg-white text-primary' : 'bg-light text-secondary border' }}">{{ $cat->questions_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Tags Sidebar -->
        <div class="dg-card p-3">
            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-tags-fill text-primary me-2"></i> Popular Tags</h6>
            <div class="d-flex flex-wrap gap-1">
                @foreach($tags as $tag)
                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                        #{{ $tag->name }} ({{ $tag->usage_count }})
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
