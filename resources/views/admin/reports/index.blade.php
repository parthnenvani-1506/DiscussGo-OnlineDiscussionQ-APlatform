@extends('layouts.admin')

@section('title', 'Moderation Reports - DiscussHub Admin')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="color: var(--admin-text-primary); font-size: 1.5rem; letter-spacing: -0.3px;">Moderation Queue</h2>
        <p class="small text-muted mb-0 mt-1">Review user-flagged content for spam, toxicity, or community guidelines breaches</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
            <i class="bi bi-speedometer2 fs-6 me-2"></i>
            <span>Dashboard</span>
        </a>
    </div>
</div>

<!-- Status Filter Tabs -->
<div class="mb-4">
    <div class="filter-tabs-group">
        <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}" class="filter-tab-item {{ $status === 'pending' ? 'active' : '' }} {{ ($counts['pending'] ?? 0) > 0 ? 'pending-alert' : '' }}">
            <i class="bi bi-shield-exclamation tab-icon"></i>
            <span>Pending Review</span>
            <span class="tab-count">{{ $counts['pending'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.reports.index', ['status' => 'resolved']) }}" class="filter-tab-item {{ $status === 'resolved' ? 'active' : '' }}">
            <i class="bi bi-check-circle tab-icon"></i>
            <span>Resolved</span>
            <span class="tab-count">{{ $counts['resolved'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.reports.index', ['status' => 'dismissed']) }}" class="filter-tab-item {{ $status === 'dismissed' ? 'active' : '' }}">
            <i class="bi bi-slash-circle tab-icon"></i>
            <span>Dismissed</span>
            <span class="tab-count">{{ $counts['dismissed'] ?? 0 }}</span>
        </a>
        <a href="{{ route('admin.reports.index', ['status' => 'all']) }}" class="filter-tab-item {{ $status === 'all' ? 'active' : '' }}">
            <i class="bi bi-collection tab-icon"></i>
            <span>All Reports</span>
            <span class="tab-count">{{ $counts['all'] ?? 0 }}</span>
        </a>
    </div>
</div>

<!-- Reports Feed -->
<div class="d-flex flex-column gap-3">
    @forelse($reports as $report)
        <div class="dg-card overflow-hidden p-4">
            <!-- Report Meta Header -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                        {{ strtoupper($report->reason) }}
                    </span>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 11px;">
                        <i class="bi {{ strtolower($report->reportable_type) === 'question' ? 'bi-question-circle' : 'bi-chat-dots' }} me-1"></i>{{ ucfirst($report->reportable_type) }} #{{ $report->reportable_id }}
                    </span>
                    @if($report->status === 'pending')
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                            <i class="bi bi-hourglass-split me-1"></i>Pending
                        </span>
                    @elseif($report->status === 'resolved')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                            <i class="bi bi-check2-circle me-1"></i>Resolved
                        </span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11px;">
                            <i class="bi bi-x-circle me-1"></i>Dismissed
                        </span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2 text-muted small">
                    <div class="user-avatar-tiny">
                        {{ strtoupper(substr($report->reporter->user_name ?? 'U', 0, 1)) }}
                    </div>
                    <span>Reported by <strong style="color: var(--admin-text-primary);">{{ $report->reporter->user_name ?? 'Anonymous' }}</strong></span>
                    <span>· <i class="bi bi-clock me-1"></i>{{ $report->created_at->diffForHumans() }}</span>
                </div>
            </div>

            <!-- Optional User Comment / Details -->
            @if($report->details)
                <div class="p-3 rounded-3 mb-3" style="background: rgba(37, 99, 235, 0.05); border: 1px dashed rgba(37, 99, 235, 0.25); font-size: 13px;">
                    <strong class="text-primary me-1"><i class="bi bi-chat-quote-fill me-1"></i>User Note:</strong> {{ $report->details }}
                </div>
            @endif

            <!-- Content Preview Container -->
            <div class="p-3 rounded-3 border mb-3" style="background: var(--admin-canvas-bg);">
                @if($report->reportable)
                    @if(strtolower($report->reportable_type) === 'question')
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <h6 class="fw-bold mb-0" style="color: var(--admin-text-primary);">{{ $report->reportable->title }}</h6>
                            <a href="{{ route('admin.questions.show', $report->reportable) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-medium small" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Inspect Question
                            </a>
                        </div>
                        <div class="small text-secondary mb-2" style="line-height: 1.6;">{{ Str::limit(strip_tags($report->reportable->description), 280) }}</div>
                        <div class="d-flex align-items-center gap-2 small text-muted pt-2 border-top border-light-subtle">
                            <span>Author:</span> <strong style="color: var(--admin-text-primary);">{{ $report->reportable->user->user_name ?? 'Unknown' }}</strong>
                        </div>
                    @else
                        <div class="small text-secondary mb-2" style="line-height: 1.6;">{!! nl2br(e(Str::limit($report->reportable->answer, 300))) !!}</div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-light-subtle small text-muted flex-wrap gap-2">
                            <div>Author: <strong style="color: var(--admin-text-primary);">{{ $report->reportable->user->user_name ?? 'Unknown' }}</strong></div>
                            @if($report->reportable->question)
                                <a href="{{ route('admin.questions.show', $report->reportable->question) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-medium small" target="_blank">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>View Parent Question
                                </a>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="d-flex align-items-center gap-3 py-2 text-muted">
                        <div class="stat-icon-wrap" style="width: 38px; height: 38px; font-size: 16px; border-radius: 10px; background: rgba(148, 163, 184, 0.15); color: #64748b;">
                            <i class="bi bi-trash3"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-secondary" style="font-size: 13px;">The reported content has already been removed</div>
                            <div class="small text-muted">The original {{ strtolower($report->reportable_type) }} no longer exists in the database. You can safely dismiss this report to complete review.</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Action Controls -->
            @if($report->status === 'pending')
                <div class="d-flex align-items-center justify-content-between pt-3 border-top flex-wrap gap-2">
                    <form action="{{ route('admin.reports.dismiss', $report) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
                            <i class="bi bi-x-circle fs-6 me-2"></i>
                            <span>Dismiss Report</span>
                        </button>
                    </form>

                    @if($report->reportable)
                        <div class="d-flex align-items-center gap-2">
                            <form action="{{ route('admin.reports.deleteContent', $report) }}" method="POST" onsubmit="return confirm('Delete this content and mark report resolved?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
                                    <i class="bi bi-trash fs-6 me-2"></i>
                                    <span>Delete Content & Resolve</span>
                                </button>
                            </form>

                            <form action="{{ route('admin.reports.deleteContent', $report) }}" method="POST" onsubmit="return confirm('Delete content AND suspend author?');">
                                @csrf
                                <input type="hidden" name="suspend_author" value="1">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
                                    <i class="bi bi-slash-circle fs-6 me-2"></i>
                                    <span>Delete & Suspend Author</span>
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @else
                <div class="d-flex align-items-center gap-2 pt-2 border-top text-muted small">
                    <i class="bi bi-info-circle text-secondary"></i>
                    <span>Report was marked as <strong style="color: var(--admin-text-primary);">{{ ucfirst($report->status) }}</strong></span>
                    @if($report->reviewed_at)
                        <span>· Reviewed {{ $report->reviewed_at->diffForHumans() }}</span>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="dg-card p-5 text-center">
            <div class="stat-icon-wrap green mx-auto mb-3" style="width: 52px; height: 52px; font-size: 24px; border-radius: 50%;">
                <i class="bi bi-shield-check"></i>
            </div>
            <h5 class="fw-bold mb-1" style="color: var(--admin-text-primary);">Moderation Queue is Clean</h5>
            <p class="small text-muted mb-0">No reports in this category. The community content is clean and well-moderated.</p>
        </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $reports->links() }}
</div>
@endsection
