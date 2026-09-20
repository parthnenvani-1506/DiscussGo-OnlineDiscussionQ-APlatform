@extends('layouts.app')

@section('title', 'DiscussHub - Open Knowledge Sharing & Community Q&A')

@section('content')
<!-- Modern 3D Knowledge Platform Hero Section -->
<div class="hero-3d-wrapper mb-4 position-relative overflow-hidden">
    <!-- Ambient Dynamic Glowing Mesh Background -->
    <div class="hero-bg-glow glow-1"></div>
    <div class="hero-bg-glow glow-2"></div>
    <div class="hero-bg-grid"></div>

    <div class="row align-items-center g-4 position-relative" style="z-index: 2;">
        <!-- Left Hero Content -->
        <div class="col-lg-7">
            <!-- Animated Live Pill Badge -->
            <div class="hero-pill-badge mb-3">
                <span class="pulse-dot"></span>
                <span class="badge-text-primary">Open Knowledge Exchange</span>
                <span class="text-muted opacity-75">·</span>
                <span class="badge-highlight"><i class="bi bi-broadcast me-1"></i> Live Discussions</span>
            </div>

            <!-- Hero Headline with Radiant Animated Gradient -->
            <h1 class="hero-title-gradient">
                Ask Questions, Share Insight &amp; <br>
                <span class="gradient-text hero-text-glow">Discover Perspectives</span>
            </h1>

            <!-- Hero Lead Subtitle -->
            <p class="hero-lead-text">
                The modern open discussion and Q&amp;A platform where curious minds meet real expertise. Explore deep questions across technology, startups, science, philosophy, and career—backed by verified peer solutions and intelligent synthesis.
            </p>

            <!-- Hero CTA Buttons Group -->
            <div class="d-flex flex-wrap align-items-center gap-3 hero-cta-group">
                <a href="{{ route('questions.create') }}" class="hero-btn-primary d-inline-flex align-items-center gap-2 text-white text-decoration-none shadow-lg">
                    <i class="bi bi-plus-circle-fill hero-btn-icon"></i>
                    <span>Ask a Question</span>
                    <i class="bi bi-arrow-right hero-arrow-icon ms-1"></i>
                </a>
                <a href="{{ route('questions.index') }}" class="hero-btn-secondary d-inline-flex align-items-center gap-2 text-decoration-none">
                    <i class="bi bi-compass"></i>
                    <span>Explore Discussions</span>
                </a>
            </div>

            <!-- Hero Metrics Bar -->
            <div class="hero-metrics-bar">
                <div class="metric-item">
                    <div class="d-flex align-items-center gap-2">
                        <span class="metric-icon-box bg-primary-subtle text-primary">
                            <i class="bi bi-collection-fill"></i>
                        </span>
                        <div>
                            <span class="metric-val text-primary">50+</span>
                            <span class="metric-label">Topics &amp; Domains</span>
                        </div>
                    </div>
                </div>
                <div class="metric-divider"></div>
                <div class="metric-item">
                    <div class="d-flex align-items-center gap-2">
                        <span class="metric-icon-box bg-success-subtle text-success">
                            <i class="bi bi-patch-check-fill"></i>
                        </span>
                        <div>
                            <span class="metric-val text-success">100%</span>
                            <span class="metric-label">Verified Insights</span>
                        </div>
                    </div>
                </div>
                <div class="metric-divider"></div>
                <div class="metric-item">
                    <div class="d-flex align-items-center gap-2">
                        <span class="metric-icon-box bg-orange-subtle text-orange">
                            <i class="bi bi-globe2"></i>
                        </span>
                        <div>
                            <span class="metric-val text-orange">Open</span>
                            <span class="metric-label">Community Driven</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 3D Isometric Discussion Scene -->
        <div class="col-lg-5">
            <div class="hero-3d-scene" id="hero-3d-scene">
                <!-- Floating Chip 1 (Top Right - Parallax Depth Layer) -->
                <div class="floating-3d-chip chip-top" id="chip-top">
                    <div class="chip-icon-box">
                        <i class="bi bi-patch-check-fill"></i>
                    </div>
                    <div>
                        <div class="chip-title">Accepted Solution</div>
                        <div class="chip-sub"><i class="bi bi-stars"></i> +50 Reputation Points</div>
                    </div>
                </div>

                <!-- Main 3D Discussion Card with Dynamic Specular Lighting -->
                <div class="hero-3d-console" id="hero-3d-card">
                    <!-- Specular Light Reflection Overlay -->
                    <div class="hero-specular-light"></div>

                    <!-- Card Header -->
                    <div class="console-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-domain">
                                <i class="bi bi-briefcase-fill me-1 text-primary"></i> Business &amp; Startups
                            </span>
                        </div>
                        <div class="console-views">
                            <span class="pulse-view-dot"></span>
                            <i class="bi bi-eye me-1"></i> 1.4k views
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="console-body">
                        <!-- Question Title -->
                        <h6 class="console-question-title">
                            What are the counterintuitive lessons founders learn when scaling from 0 to $1M ARR?
                        </h6>

                        <!-- Author Row -->
                        <div class="console-author-row">
                            <div class="author-avatar-badge">
                                <span>S</span>
                                <span class="author-online-dot"></span>
                            </div>
                            <div class="author-info">
                                <span class="author-name">Sarah Dev</span>
                                <span class="author-title">· Product Strategist</span>
                            </div>
                        </div>

                        <!-- Highlighted Accepted Solution Bubble -->
                        <div class="console-solution-bubble">
                            <div class="quote-watermark"><i class="bi bi-quote"></i></div>
                            <p class="solution-quote">
                                "Retention always precedes acquisition. If your 30-day retention curve doesn't flatten, pouring in users only leaks value..."
                            </p>
                        </div>

                        <!-- Card Footer Reactions & Consensus -->
                        <div class="console-footer-bar">
                            <button type="button" class="hero-like-btn" id="hero-demo-like-btn" title="Simulate Like Interaction">
                                <i class="bi bi-heart-fill text-danger hero-heart-icon"></i>
                                <span class="hero-like-text"><span id="hero-demo-like-count">48</span> Likes</span>
                            </button>
                            <span class="hero-consensus-badge">
                                <i class="bi bi-stars hero-stars-icon"></i> Verified Consensus
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Floating Chip 2 (Bottom Left - Parallax Depth Layer) -->
                <div class="floating-3d-chip chip-bottom" id="chip-bottom">
                    <div class="chip-icon-box">
                        <i class="bi bi-chat-quote-fill"></i>
                    </div>
                    <div>
                        <div class="chip-title">Smart Consensus</div>
                        <div class="chip-sub"><i class="bi bi-cpu"></i> Multi-Perspective Synthesis</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Feed Column -->
    <div class="col-lg-8">
        <!-- Feed Filter Navigation Tabs Capsule -->
        <div class="dg-feed-header-bar mb-3">
            <div class="dg-feed-tabs-wrapper">
                <ul class="nav nav-pills dg-feed-nav-pills gap-1 flex-nowrap overflow-x-auto">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link dg-feed-tab {{ $feedFilter === 'recommended' ? 'active' : '' }}" href="{{ route('home', ['feed' => 'recommended']) }}">
                                <i class="bi bi-sparkles tab-icon-sparkle me-1"></i> Recommended For You
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link dg-feed-tab {{ $feedFilter === 'following' ? 'active' : '' }}" href="{{ route('home', ['feed' => 'following']) }}">
                                <i class="bi bi-person-check tab-icon-following me-1"></i> Following
                            </a>
                        </li>
                    @endauth
                    <li class="nav-item">
                        <a class="nav-link dg-feed-tab {{ $feedFilter === 'latest' ? 'active' : '' }}" href="{{ route('home', ['feed' => 'latest']) }}">
                            <i class="bi bi-clock tab-icon-clock me-1"></i> Latest
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link dg-feed-tab {{ $feedFilter === 'trending' ? 'active' : '' }}" href="{{ route('home', ['feed' => 'trending']) }}">
                            <i class="bi bi-fire tab-icon-fire me-1"></i> Trending
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link dg-feed-tab {{ $feedFilter === 'unanswered' ? 'active' : '' }}" href="{{ route('home', ['feed' => 'unanswered']) }}">
                            <i class="bi bi-chat-left-dots tab-icon-chat me-1"></i> Unanswered
                        </a>
                    </li>
                </ul>
            </div>

            <div class="dg-feed-counter-pill d-none d-sm-inline-flex">
                <span class="counter-dot"></span>
                <span>{{ $questions->total() }} discussions</span>
            </div>
        </div>

        <!-- Question Cards List -->
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

                    <!-- Row 3: Question Description Snippet (Clean & Compact) -->
                    <p class="question-desc-snippet mb-2">
                        {{ Str::limit(strip_tags($question->description), 140) }}
                    </p>

                    <!-- Row 4: Question Footer - Tags Cloud & Author Meta -->
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
                        <i class="bi bi-chat-square-dots text-primary display-4"></i>
                    </div>
                    @if($feedFilter === 'following')
                        <h5 class="fw-bold text-dark mb-1">No discussions from people you follow</h5>
                        <p class="small text-secondary mb-4">Follow more users to discover their perspectives and insights.</p>
                        <a href="{{ route('questions.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-compass me-1"></i> Explore Discussions
                        </a>
                    @else
                        <h5 class="fw-bold text-dark mb-1">No discussions found in this feed</h5>
                        <p class="small text-secondary mb-4">Be the pioneer to start a discussion or ask a question.</p>
                        <a href="{{ route('questions.create') }}" class="dg-btn-cta px-4 text-white text-decoration-none">
                            <i class="bi bi-plus-circle me-1"></i> Ask a Question
                        </a>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    <!-- Right Sidebar Column -->
    <div class="col-lg-4">
        <div class="dg-sticky-sidebar">
            <!-- Top Categories Widget -->
            <div class="dg-card p-3 mb-4 sidebar-widget-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-fill text-primary me-2"></i> Topics &amp; Categories</h6>
                    <a href="{{ route('categories.index') }}" class="small text-primary text-decoration-none fw-semibold">View all <i class="bi bi-chevron-right small"></i></a>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($topCategories as $category)
                        <a href="{{ route('categories.show', $category->slug) }}" class="sidebar-category-row text-decoration-none">
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
                    @foreach($popularTags as $tag)
                        <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                            <span class="tag-hash">#</span>{{ $tag->name }} <span class="tag-count ms-1">({{ $tag->usage_count }})</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Top Contributors Leaderboard -->
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
