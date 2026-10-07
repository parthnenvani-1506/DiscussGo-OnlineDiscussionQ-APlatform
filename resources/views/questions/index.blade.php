@extends('layouts.app')

@section('title', 'Explore Technical Questions & Discussions - DiscussHub')

@section('content')
<!-- Questions Index Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--text-primary);"><i class="bi bi-compass text-primary me-2"></i> Explore Discussions</h2>
        <p class="text-secondary small mb-0">{{ $questions->total() }} curated technical discussions across all engineering categories</p>
    </div>
    @auth
        <a href="{{ route('questions.create') }}" class="hero-btn-primary d-inline-flex align-items-center gap-2 text-white text-decoration-none shadow-sm flex-shrink-0">
            <i class="bi bi-plus-circle-fill hero-btn-icon"></i>
            <span>Ask a Question</span>
            <i class="bi bi-arrow-right hero-arrow-icon ms-1"></i>
        </a>
    @endauth
</div>

<!-- Filters & Sorting Bar with Realtime Keyword Search -->
<div class="dg-card p-3 mb-4">
    <form action="{{ route('questions.index') }}" method="GET" class="row g-2 align-items-center dg-realtime-search-form" id="dg-questions-filter-form">
        <!-- Hidden inputs to synchronize custom dropdown states -->
        <input type="hidden" name="status" id="input-filter-status" value="{{ request('status') }}">
        <input type="hidden" name="category" id="input-filter-category" value="{{ request('category') }}">
        <input type="hidden" name="sort" id="input-filter-sort" value="{{ $sort }}">

        <!-- Keyword filter: Unified Single-Unit Search Bar -->
        <div class="col-lg-4 col-md-12">
            <div class="dg-search-bar-unified">
                <i class="bi bi-search search-icon"></i>
                <input type="text" 
                       name="q" 
                       class="search-input dg-realtime-input" 
                       placeholder="Search discussions & keywords..." 
                       value="{{ request('q') }}" 
                       autocomplete="off">
                @if(request('q'))
                    <a href="{{ route('questions.index', request()->except('q')) }}" class="text-muted text-decoration-none small ms-1" title="Clear search">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>
                @endif
            </div>
        </div>

        <!-- Status Filter Custom Dropdown -->
        <div class="col-sm-4 col-lg-2 col-md-4">
            <div class="dropdown">
                @php
                    $statusLabel = 'All Statuses';
                    if (request('status') === 'answered') $statusLabel = 'Solved';
                    elseif (request('status') === 'unanswered') $statusLabel = 'Unanswered';
                @endphp
                <button class="dg-custom-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="dropdown-status-btn">
                    <span class="dropdown-btn-label">
                        <span id="label-status">{{ $statusLabel }}</span>
                    </span>
                    <i class="bi bi-chevron-down dropdown-chevron"></i>
                </button>
                <ul class="dropdown-menu dg-custom-dropdown-menu">
                    <li>
                        <a class="dropdown-item {{ !request('status') ? 'active' : '' }}" href="javascript:void(0)" onclick="selectFilterOption('status', '', 'All Statuses')">
                            <span>All Statuses</span>
                            @if(!request('status')) <i class="bi bi-check2 text-primary"></i> @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('status') === 'answered' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectFilterOption('status', 'answered', 'Solved')">
                            <span><i class="bi bi-check2-circle text-success me-1"></i> Solved</span>
                            @if(request('status') === 'answered') <i class="bi bi-check2 text-primary"></i> @endif
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item {{ request('status') === 'unanswered' ? 'active' : '' }}" href="javascript:void(0)" onclick="selectFilterOption('status', 'unanswered', 'Unanswered')">
                            <span><i class="bi bi-chat-left-dots text-warning me-1"></i> Unanswered</span>
                            @if(request('status') === 'unanswered') <i class="bi bi-check2 text-primary"></i> @endif
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Category Filter Custom Dropdown -->
        <div class="col-sm-4 col-lg-3 col-md-4">
            <div class="dropdown">
                @php
                    $selectedCategory = $categories->firstWhere('slug', request('category'));
                    $categoryLabel = $selectedCategory ? $selectedCategory->name : 'All Categories';
                @endphp
                <button class="dg-custom-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="dropdown-category-btn">
                    <span class="dropdown-btn-label">
                        <span id="label-category">{{ $categoryLabel }}</span>
                    </span>
                    <i class="bi bi-chevron-down dropdown-chevron"></i>
                </button>
                <ul class="dropdown-menu dg-custom-dropdown-menu">
                    <li>
                        <a class="dropdown-item {{ !request('category') ? 'active' : '' }}" href="javascript:void(0)" onclick="selectFilterOption('category', '', 'All Categories')">
                            <span>All Categories</span>
                            <span class="badge-count">{{ $questions->total() }}</span>
                        </a>
                    </li>
                    @foreach($categories as $category)
                        <li>
                            <a class="dropdown-item {{ request('category') === $category->slug ? 'active' : '' }}" href="javascript:void(0)" onclick="selectFilterOption('category', '{{ $category->slug }}', '{{ addslashes($category->name) }}')">
                                <span class="text-truncate">{{ $category->name }}</span>
                                <span class="badge-count">{{ $category->questions_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Sort Filter Custom Dropdown -->
        <div class="col-sm-4 col-lg-3 col-md-4">
            <div class="dropdown">
                @php
                    $sortLabels = [
                        'newest' => 'Newest',
                        'votes' => 'Most Liked',
                        'answers' => 'Most Answers',
                        'views' => 'Most Views',
                        'oldest' => 'Oldest',
                    ];
                    $currentSortLabel = $sortLabels[$sort] ?? 'Newest';
                @endphp
                <button class="dg-custom-dropdown-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="dropdown-sort-btn">
                    <span class="dropdown-btn-label">
                        <span id="label-sort">{{ $currentSortLabel }}</span>
                    </span>
                    <i class="bi bi-chevron-down dropdown-chevron"></i>
                </button>
                <ul class="dropdown-menu dg-custom-dropdown-menu">
                    @foreach($sortLabels as $sKey => $sVal)
                        <li>
                            <a class="dropdown-item {{ $sort === $sKey ? 'active' : '' }}" href="javascript:void(0)" onclick="selectFilterOption('sort', '{{ $sKey }}', '{{ $sVal }}')">
                                <span>{{ $sVal }}</span>
                                @if($sort === $sKey) <i class="bi bi-check2 text-primary"></i> @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </form>
</div>

<div class="row g-4">
    <!-- Main Content Area (Questions List) -->
    <div class="col-lg-8">
        <!-- Realtime Questions Listing Container -->
        <div id="dg-realtime-results-container" class="dg-realtime-results-wrapper">
            <div class="d-flex flex-column gap-3">
                @forelse($questions as $question)
                    <div class="question-item" data-href="{{ route('questions.show', [$question->id, $question->slug]) }}">
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

                        <!-- Row 4: Question Footer - Tags & Author Meta -->
                        <div class="question-footer-row">
                            <!-- Tags Cloud -->
                            <div class="d-flex flex-wrap gap-1 align-items-center">
                                @foreach($question->tags as $tag)
                                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                                        <span class="tag-hash">#</span>{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>

                            <!-- Meta Info & Author -->
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
                    <div class="dg-card p-5 text-center empty-feed-card">
                        <div class="empty-icon-box mb-3">
                            <i class="bi bi-search text-primary display-4"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">No discussions matching your criteria</h5>
                        <p class="small text-secondary mb-4">Try adjusting your filters, clearing search keywords, or selecting another category.</p>
                        <a href="{{ route('questions.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Sidebar Column -->
    <div class="col-lg-4">
        <div class="dg-sticky-sidebar">
            <!-- Topics & Categories Widget -->
            <div class="dg-card p-3 mb-4 sidebar-widget-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-fill text-primary me-2"></i> Topics &amp; Categories</h6>
                    <a href="{{ route('categories.index') }}" class="small text-primary text-decoration-none fw-semibold">View all <i class="bi bi-chevron-right small"></i></a>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($categories->take(8) as $category)
                        <a href="{{ route('categories.show', $category->slug) }}" class="sidebar-category-row text-decoration-none {{ request('category') === $category->slug ? 'active' : '' }}">
                            <div class="d-flex align-items-center gap-2">
                                <span class="sidebar-cat-icon">
                                    <i class="bi bi-folder-fill"></i>
                                </span>
                                <span class="fw-semibold text-dark small sidebar-cat-name">{{ $category->name }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge sidebar-cat-badge">{{ $category->questions_count }}</span>
                                <i class="bi bi-arrow-right-short sidebar-cat-arrow"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Popular Tags Cloud -->
            <div class="dg-card p-3 mb-4 sidebar-widget-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-tags-fill text-primary me-2"></i> Popular Tags</h6>
                    <a href="{{ route('tags.index') }}" class="small text-primary text-decoration-none fw-semibold">All tags <i class="bi bi-chevron-right small"></i></a>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($tags as $tag)
                        <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                            <span class="tag-hash">#</span>{{ $tag->name }} <span class="tag-count ms-1">({{ $tag->usage_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Top Contributors Leaderboard -->
            @if(isset($topUsers) && $topUsers->count() > 0)
                <div class="dg-card p-3 sidebar-widget-card">
                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-trophy-fill text-warning me-2"></i> Top Contributors</h6>
                    <div class="d-flex flex-column gap-3">
                        @foreach($topUsers as $index => $topUser)
                            <div class="d-flex align-items-center justify-content-between leaderboard-row">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="leaderboard-rank-badge rank-{{ $index + 1 }}">
                                        @if($index === 0)
                                            <i class="bi bi-trophy-fill text-warning"></i>
                                        @elseif($index === 1)
                                            <i class="bi bi-award-fill text-secondary"></i>
                                        @elseif($index === 2)
                                            <i class="bi bi-award-fill text-bronze"></i>
                                        @else
                                            #{{ $index + 1 }}
                                        @endif
                                    </span>
                                    @if($topUser->profile_image && $topUser->profile_image !== 'default_profile.png')
                                        <img src="{{ asset('profiles/' . $topUser->profile_image) }}" class="rounded-circle object-fit-cover leaderboard-avatar" width="34" height="34" alt="avatar">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold small leaderboard-avatar" style="width: 34px; height: 34px;">
                                            {{ strtoupper(substr($topUser->user_name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <a href="{{ route('users.show', $topUser->id) }}" class="fw-semibold text-dark text-decoration-none small d-block leaderboard-username">
                                            {{ $topUser->user_name }}
                                        </a>
                                        <span class="badge bg-light text-secondary border py-0 px-1" style="font-size: 0.65rem;">
                                            {{ ucfirst($topUser->level ?? 'newcomer') }}
                                        </span>
                                    </div>
                                </div>
                                <span class="reputation-badge rep-badge-fmt"
                                    data-rep="{{ $topUser->reputation }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ number_format($topUser->reputation) }} reputation points">
                                    <i class="bi bi-stars"></i> <span class="rep-value">{{ $topUser->reputation }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
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

@push('scripts')
<script>
function selectFilterOption(type, value, label) {
    const input = document.getElementById('input-filter-' + type);
    const labelEl = document.getElementById('label-' + type);
    const form = document.getElementById('dg-questions-filter-form');
    
    if (input) {
        input.value = value;
    }
    if (labelEl) {
        labelEl.textContent = label;
    }
    
    // Update active class on dropdown items
    const dropdownMenu = input ? input.closest('form').querySelector('#dropdown-' + type + '-btn')?.nextElementSibling : null;
    if (dropdownMenu) {
        dropdownMenu.querySelectorAll('.dropdown-item').forEach(item => {
            item.classList.remove('active');
            const check = item.querySelector('.bi-check2');
            if (check) check.remove();
        });
        
        // Find clicked element or active item
        const clickedItem = event && event.target ? event.target.closest('.dropdown-item') : null;
        if (clickedItem) {
            clickedItem.classList.add('active');
            if (!clickedItem.querySelector('.bi-check2') && !clickedItem.querySelector('.badge-count')) {
                const checkIcon = document.createElement('i');
                checkIcon.className = 'bi bi-check2 text-primary';
                clickedItem.appendChild(checkIcon);
            }
        }
    }
    
    if (form) {
        const textInput = form.querySelector('.dg-realtime-input');
        if (textInput) {
            textInput.dispatchEvent(new Event('input', { bubbles: true }));
        } else {
            form.submit();
        }
    }
}
</script>
@endpush
