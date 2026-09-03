@extends('layouts.app')

@section('title', $user->user_name . ' - Profile - DiscussHub')

@section('content')
<div class="row g-4">
    <!-- User Profile Header Card -->
    <div class="col-12">
        <div class="dg-card p-4">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start justify-content-between gap-4">
                <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3 text-center text-md-start">
                    @if($user->profile_image)
                        <img src="{{ asset('profiles/' . $user->profile_image) }}" class="rounded-circle object-fit-cover shadow-sm" width="90" height="90" alt="avatar">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 90px; height: 90px; font-size: 2.2rem;">
                            {{ strtoupper(substr($user->user_name, 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                            <h3 class="fw-bold text-dark mb-0">{{ $user->user_name }}</h3>
                            <span class="badge bg-primary rounded-pill">{{ ucfirst($user->level ?? 'newcomer') }}</span>
                        </div>
                        <div class="text-secondary small mb-2 d-flex align-items-center justify-content-center justify-content-md-start gap-3 flex-wrap">
                            @if($user->city)
                                <span><i class="bi bi-geo-alt me-1"></i> {{ $user->city }}</span>
                            @endif
                            <span><i class="bi bi-calendar3 me-1"></i> Joined {{ $user->created_at->format('M Y') }}</span>
                            <span class="reputation-badge rep-badge-fmt"
                                data-rep="{{ $user->reputation }}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="bottom"
                                title="{{ number_format($user->reputation) }} reputation points">
                                <i class="bi bi-stars"></i> <span class="rep-value">{{ $user->reputation }}</span> Reputation
                            </span>
                        </div>
                        @if($user->bio)
                            <p class="text-secondary small mb-0" style="max-width: 600px;">{{ $user->bio }}</p>
                        @endif
                    </div>
                </div>

                @if($isOwner)
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-pencil me-1"></i> Edit Profile
                        </a>
                        @if($user->role === 'moderator')
                            <a href="{{ route('moderator.dashboard') }}" class="btn btn-warning rounded-pill px-4">
                                <i class="bi bi-shield-half me-1"></i> Moderator Panel
                            </a>
                        @endif
                    </div>
                @else
                    @auth
                        <button class="btn {{ auth()->user()->isFollowing($user) ? 'btn-outline-secondary' : 'btn-primary' }} rounded-pill px-4 btn-follow-toggle"
                            data-user-id="{{ $user->id }}">
                            @if(auth()->user()->isFollowing($user))
                                <i class="bi bi-person-check me-1"></i> Following
                            @else
                                <i class="bi bi-person-plus me-1"></i> Follow
                            @endif
                        </button>
                    @endauth
                @endif
            </div>

            <!-- Stats Bar -->
            <div class="row g-3 mt-4 pt-3 border-top text-center">
                <div class="col">
                    <div class="fw-bold fs-5 text-dark">{{ $stats['questions_count'] }}</div>
                    <div class="text-muted small">Questions</div>
                </div>
                <div class="col">
                    <div class="fw-bold fs-5 text-dark">{{ $stats['answers_count'] }}</div>
                    <div class="text-muted small">Answers</div>
                </div>
                <div class="col">
                    <div class="fw-bold fs-5 text-success">{{ $stats['accepted_answers_count'] }}</div>
                    <div class="text-muted small">Accepted</div>
                </div>
                <div class="col">
                    <a href="{{ route('users.followers', $user->id) }}" class="text-decoration-none">
                        <div class="fw-bold fs-5 text-dark">{{ $user->followers_count }}</div>
                        <div class="text-muted small">Followers</div>
                    </a>
                </div>
                <div class="col">
                    <a href="{{ route('users.following', $user->id) }}" class="text-decoration-none">
                        <div class="fw-bold fs-5 text-dark">{{ $user->following_count }}</div>
                        <div class="text-muted small">Following</div>
                    </a>
                </div>
                <div class="col">
                    <div class="fw-bold fs-5 text-warning">{{ $stats['badges_count'] }}</div>
                    <div class="text-muted small">Badges</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Badges Showcase -->
    <div class="col-12">
        <div class="dg-card p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-award-fill text-warning me-2"></i> Earned Badges</h5>
            @if($user->badges->isNotEmpty())
                <div class="row g-3">
                    @foreach($user->badges as $badge)
                        <div class="col-md-4 col-sm-6">
                            <div class="p-3 rounded border bg-light d-flex align-items-center gap-3">
                                <div class="p-2 rounded-circle bg-warning bg-opacity-25 text-warning flex-shrink-0">
                                    <i class="{{ $badge->icon ?? 'bi bi-patch-check-fill' }} fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">{{ $badge->name }}</div>
                                    <div class="text-secondary small" style="font-size: 0.75rem;">{{ $badge->description }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted small mb-0">No badges earned yet. Ask questions, post helpful answers, and receive upvotes to unlock achievements!</p>
            @endif
        </div>
    </div>

    <!-- Activity Tabs (Questions / Answers / Reputation Log) -->
    <div class="col-12">
        <div class="dg-card p-4">
            <ul class="nav nav-tabs border-bottom mb-4" id="profileTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-semibold" id="questions-tab" data-bs-toggle="tab" data-bs-target="#tab-questions" type="button">
                        Questions ({{ $questions->total() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold" id="answers-tab" data-bs-toggle="tab" data-bs-target="#tab-answers" type="button">
                        Answers ({{ $answers->total() }})
                    </button>
                </li>
                @if($isOwner)
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="reputation-tab" data-bs-toggle="tab" data-bs-target="#tab-reputation" type="button">
                            Reputation History
                        </button>
                    </li>
                @endif
            </ul>

            <div class="tab-content" id="profileTabContent">
                <!-- Questions Tab -->
                <div class="tab-pane fade show active" id="tab-questions" role="tabpanel">
                    @forelse($questions as $question)
                        <div class="py-3 border-bottom d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('questions.show', [$question->id, $question->slug]) }}" class="fw-semibold text-dark text-decoration-none">
                                    {{ $question->title }}
                                </a>
                                <div class="d-flex align-items-center gap-2 small text-muted mt-1">
                                    <span>{{ $question->created_at->format('M d, Y') }}</span>
                                    <span>·</span>
                                    <span>{{ $question->category->name }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border"><i class="bi bi-heart-fill text-danger" style="font-size:0.65rem;"></i> {{ $question->vote_score }} likes</span>
                                <span class="badge {{ $question->is_answered ? 'bg-success' : 'bg-light text-secondary border' }}">
                                    {{ $question->answer_count }} answers
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small py-3 mb-0">No questions posted yet.</p>
                    @endforelse

                    <div class="mt-3">
                        {{ $questions->links() }}
                    </div>
                </div>

                <!-- Answers Tab -->
                <div class="tab-pane fade" id="tab-answers" role="tabpanel">
                    @forelse($answers as $answer)
                        <div class="py-3 border-bottom">
                            <a href="{{ route('questions.show', [$answer->question->id, $answer->question->slug]) }}" class="fw-semibold text-dark text-decoration-none small d-block mb-1">
                                On: {{ $answer->question->title }}
                            </a>
                            <p class="text-secondary small mb-1 text-truncate">{{ Str::limit(strip_tags($answer->answer), 140) }}</p>
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <span>{{ $answer->created_at->diffForHumans() }}</span>
                                <span>·</span>
                                <span class="badge bg-light text-dark border"><i class="bi bi-heart-fill text-danger" style="font-size:0.65rem;"></i> {{ $answer->vote_score }} likes</span>
                                @if($answer->is_accepted)
                                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Accepted</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small py-3 mb-0">No answers posted yet.</p>
                    @endforelse

                    <div class="mt-3">
                        {{ $answers->links() }}
                    </div>
                </div>

                <!-- Reputation History Tab -->
                @if($isOwner)
                    <div class="tab-pane fade" id="tab-reputation" role="tabpanel">
                        <div class="d-flex flex-column gap-2">
                            @forelse($reputations as $rep)
                                <div class="p-2 rounded bg-light border d-flex align-items-center justify-content-between">
                                    <div class="small text-dark">
                                        {{ $rep->reason }}
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge {{ $rep->points >= 0 ? 'bg-success' : 'bg-danger' }}">
                                            {{ $rep->points >= 0 ? '+' . $rep->points : $rep->points }}
                                        </span>
                                        <span class="text-muted" style="font-size: 0.75rem;">{{ $rep->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-muted small py-3 mb-0">No reputation transactions recorded yet.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
