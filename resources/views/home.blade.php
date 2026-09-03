@extends('layouts.app')

@section('title', 'DiscussHub - Open Knowledge Sharing & Community Q&A')

@section('content')
<!-- Modern 3D Knowledge Platform Hero Section -->
<div class="hero-3d-wrapper mb-4">
    <div class="row align-items-center g-4">
        <!-- Left Hero Content -->
        <div class="col-lg-7">
            <div class="hero-pill-badge mb-3">
                <span class="pulse-dot"></span>
                <span>Open Knowledge Exchange</span>
                <span class="text-muted ms-1">·</span>
                <span class="text-primary fw-bold">Live Discussions</span>
            </div>

            <h1 class="hero-title-gradient">
                Ask Questions, Share Insight & <br><span class="gradient-text">Discover Perspectives</span>
            </h1>

            <p class="hero-lead-text">
                The modern open discussion and Q&amp;A platform where curious minds meet real expertise. Explore deep questions across technology, startups, science, philosophy, and career—backed by verified peer solutions and intelligent synthesis.
            </p>

            <div class="d-flex flex-wrap align-items-center gap-3">
                <a href="{{ route('questions.create') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-plus-circle"></i> Ask a Question
                </a>
                <a href="{{ route('questions.index') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                    <i class="bi bi-compass"></i> Explore Discussions
                </a>
            </div>

            <!-- Hero Metrics Bar -->
            <div class="hero-metrics-bar">
                <div class="metric-item">
                    <span class="metric-val text-primary">50+</span>
                    <span class="metric-label">Topics &amp; Domains</span>
                </div>
                <div class="metric-item">
                    <span class="metric-val text-success">100%</span>
                    <span class="metric-label">Verified Insights</span>
                </div>
                <div class="metric-item">
                    <span class="metric-val text-dark">Open</span>
                    <span class="metric-label">Community Driven</span>
                </div>
            </div>
        </div>

        <!-- Right 3D Isometric Discussion Scene -->
        <div class="col-lg-5">
            <div class="hero-3d-scene">
                <!-- Floating Chip 1 (Top) -->
                <div class="floating-3d-chip chip-top">
                    <div class="p-1 rounded bg-success bg-opacity-25 text-success">
                        <i class="bi bi-patch-check-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="text-white">Accepted Solution</div>
                        <div class="text-success" style="font-size: 0.65rem;">+50 Reputation Points</div>
                    </div>
                </div>

                <!-- Main 3D Discussion Card -->
                <div class="hero-3d-console">
                    <div class="console-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-25 text-primary border border-primary border-opacity-25 py-1 px-2" style="font-size: 0.7rem;">
                                <i class="bi bi-briefcase me-1"></i> Business &amp; Startups
                            </span>
                        </div>
                        <div class="console-title text-muted"><i class="bi bi-eye me-1"></i> 1.4k views</div>
                    </div>
                    <div class="console-body p-3">
                        <div class="fw-bold text-white fs-6 mb-2">
                            What are the counterintuitive lessons founders learn when scaling from 0 to $1M ARR?
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle bg-warning text-dark fw-bold d-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.65rem;">S</div>
                            <span class="small text-secondary" style="font-size: 0.75rem;">Sarah Dev · Product Strategist</span>
                        </div>
                        <div class="p-2 rounded bg-dark bg-opacity-50 text-secondary border border-secondary border-opacity-25 small mb-2" style="font-size: 0.775rem; line-height: 1.5;">
                            "Retention always precedes acquisition. If your 30-day retention curve doesn't flatten, pouring in users only leaks value..."
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.725rem;">
                            <span class="text-success fw-semibold"><i class="bi bi-heart-fill text-danger"></i> 48 Likes</span>
                            <span class="text-primary"><i class="bi bi-stars"></i> Verified Consensus</span>
                        </div>
                    </div>
                </div>

                <!-- Floating Chip 2 (Bottom) -->
                <div class="floating-3d-chip chip-bottom">
                    <div class="p-1 rounded bg-primary bg-opacity-25 text-primary">
                        <i class="bi bi-chat-quote-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="text-white">Smart Consensus</div>
                        <div class="text-primary" style="font-size: 0.65rem;">Multi-Perspective Synthesis</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Feed Column -->
    <div class="col-lg-8">
        <!-- Feed Filter Navigation Tabs -->
        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
            <ul class="nav nav-pills gap-1">
                @auth
                    <li class="nav-item">
                        <a class="nav-link rounded-pill py-1 px-3 small fw-medium {{ $feedFilter === 'recommended' ? 'active' : 'text-secondary' }}" href="{{ route('home', ['feed' => 'recommended']) }}">
                            <i class="bi bi-sparkles me-1"></i> Recommended For You
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link rounded-pill py-1 px-3 small fw-medium {{ $feedFilter === 'following' ? 'active' : 'text-secondary' }}" href="{{ route('home', ['feed' => 'following']) }}">
                            <i class="bi bi-person-check me-1"></i> Following
                        </a>
                    </li>
                @endauth
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-1 px-3 small fw-medium {{ $feedFilter === 'latest' ? 'active' : 'text-secondary' }}" href="{{ route('home', ['feed' => 'latest']) }}">
                        <i class="bi bi-clock me-1"></i> Latest
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-1 px-3 small fw-medium {{ $feedFilter === 'trending' ? 'active' : 'text-secondary' }}" href="{{ route('home', ['feed' => 'trending']) }}">
                        <i class="bi bi-fire text-danger me-1"></i> Trending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill py-1 px-3 small fw-medium {{ $feedFilter === 'unanswered' ? 'active' : 'text-secondary' }}" href="{{ route('home', ['feed' => 'unanswered']) }}">
                        <i class="bi bi-chat-left-dots me-1"></i> Unanswered
                    </a>
                </li>
            </ul>

            <span class="text-secondary small d-none d-sm-inline">{{ $questions->total() }} discussions</span>
        </div>

        <!-- Question Cards List -->
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
                            @if($question->ai_summary)
                                <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-stars"></i> Synthesized</span>
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
                            <!-- Tags -->
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($question->tags as $tag)
                                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>

                            <!-- Meta Info & Author -->
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
                                <span><i class="bi bi-heart-fill text-danger"></i> {{ $question->vote_score }}</span>
                                <span>·</span>
                                <span><i class="bi bi-eye"></i> {{ $question->view_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="dg-card p-5 text-center">
                    <i class="bi bi-chat-square-dots text-muted display-4 mb-3 d-block"></i>
                    @if($feedFilter === 'following')
                        <h5 class="text-secondary fw-bold">No discussions from people you follow</h5>
                        <p class="small text-muted mb-4">Follow more users to see their questions here.</p>
                        <a href="{{ route('questions.index') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-compass me-1"></i> Explore Discussions
                        </a>
                    @else
                        <h5 class="text-secondary fw-bold">No discussions found in this feed</h5>
                        <p class="small text-muted mb-4">Be the pioneer to start a discussion or ask a question.</p>
                        <a href="{{ route('questions.create') }}" class="btn btn-primary rounded-pill px-4">
                            <i class="bi bi-plus-circle me-1"></i> Ask a Question
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4 d-flex justify-content-center">
            {{ $questions->links() }}
        </div>
    </div>

    <!-- Right Sidebar Column -->
    <div class="col-lg-4">
        <!-- Community Insights Card -->
        <div class="ai-assistant-card mb-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-primary text-white"><i class="bi bi-stars me-1"></i> Intelligent Platform</span>
            </div>
            <h6 class="fw-bold mb-2 text-dark">Curated Knowledge</h6>
            <p class="small text-secondary mb-3">
                Explore multifaceted discussions, real-world mental models, and multi-answer consensus summaries tailored to your interests.
            </p>
            <div class="small fw-semibold text-primary">
                <i class="bi bi-shield-check me-1"></i> Peer-reviewed and community verified
            </div>
        </div>

        <!-- Top Categories Widget -->
        <div class="dg-card p-3 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-grid-fill text-primary me-2"></i> Topics &amp; Categories</h6>
                <a href="{{ route('categories.index') }}" class="small text-primary text-decoration-none">View all</a>
            </div>
            <div class="d-flex flex-column gap-2">
                @foreach($topCategories as $category)
                    <a href="{{ route('categories.show', $category->slug) }}" class="d-flex align-items-center justify-content-between text-decoration-none p-2 rounded bg-light border">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge p-2 rounded bg-primary-subtle text-primary">
                                <i class="bi bi-folder"></i>
                            </span>
                            <span class="fw-medium text-dark small">{{ $category->name }}</span>
                        </div>
                        <span class="badge bg-light text-secondary border small">{{ $category->questions_count }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Popular Tags Cloud -->
        <div class="dg-card p-3 mb-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-tags-fill text-primary me-2"></i> Popular Tags</h6>
                <a href="{{ route('tags.index') }}" class="small text-primary text-decoration-none">All tags</a>
            </div>
            <div class="d-flex flex-wrap gap-1">
                @foreach($popularTags as $tag)
                    <a href="{{ route('tags.show', $tag->slug) }}" class="tag-badge">
                        #{{ $tag->name }} <span class="opacity-75 ms-1">({{ $tag->usage_count }})</span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Top Contributors Leaderboard -->
        <div class="dg-card p-3">
            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-trophy-fill text-warning me-2"></i> Top Contributors</h6>
            <div class="d-flex flex-column gap-3">
                @foreach($topUsers as $index => $topUser)
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold small text-muted" style="width: 16px;">#{{ $index + 1 }}</span>
                            @if($topUser->profile_image && $topUser->profile_image !== 'default_profile.png')
                                <img src="{{ asset('profiles/' . $topUser->profile_image) }}" class="rounded-circle object-fit-cover" width="32" height="32" alt="avatar">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold small" style="width: 32px; height: 32px;">
                                    {{ strtoupper(substr($topUser->user_name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('users.show', $topUser->id) }}" class="fw-semibold text-dark text-decoration-none small d-block">
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
@endsection
