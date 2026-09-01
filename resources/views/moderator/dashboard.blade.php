@extends('layouts.moderator')
@section('title', 'Moderator Dashboard - DiscussHub')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Moderator Dashboard</h2>
        <p class="text-secondary small mb-0">Welcome back, {{ auth()->user()->user_name }}. Review flagged content and handle community reports.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('moderator.ai-queue') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="bi bi-robot me-1"></i> AI Queue
            @if($stats['ai_flags_pending'] > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $stats['ai_flags_pending'] }}</span>
            @endif
        </a>
        <a href="{{ route('moderator.report-queue') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
            <i class="bi bi-flag me-1"></i> Reports
            @if($stats['reports_pending'] > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $stats['reports_pending'] }}</span>
            @endif
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">AI Flags Pending</div>
                    <h3 class="fw-bold {{ $stats['ai_flags_pending'] > 0 ? 'text-danger' : 'text-dark' }} mb-0 mt-1">{{ $stats['ai_flags_pending'] }}</h3>
                    <span class="text-muted small">Requires review</span>
                </div>
                <div class="p-3 rounded bg-primary-subtle text-primary fs-3">
                    <i class="bi bi-robot"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">User Reports</div>
                    <h3 class="fw-bold {{ $stats['reports_pending'] > 0 ? 'text-warning' : 'text-dark' }} mb-0 mt-1">{{ $stats['reports_pending'] }}</h3>
                    <span class="text-muted small">Pending review</span>
                </div>
                <div class="p-3 rounded bg-warning-subtle text-warning fs-3">
                    <i class="bi bi-flag-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">Actions Today</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['actions_today'] }}</h3>
                    <span class="text-muted small">Moderation decisions</span>
                </div>
                <div class="p-3 rounded bg-success-subtle text-success fs-3">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">Total Actions</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total_actions'] }}</h3>
                    <span class="text-muted small">All time</span>
                </div>
                <div class="p-3 rounded bg-secondary-subtle text-secondary fs-3">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Actions + Guidelines -->
<div class="row g-4">
    <div class="col-lg-8">
        <div class="dg-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0">My Recent Actions</h5>
                <a href="{{ route('moderator.history') }}" class="small text-primary text-decoration-none">View all history</a>
            </div>
            @if($recentActions->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Action</th>
                                <th>Target</th>
                                <th>Reason</th>
                                <th>Source</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActions as $action)
                                <tr>
                                    <td>
                                        @php
                                            $color = match($action->action_type) {
                                                'remove_question','remove_answer' => 'danger',
                                                'warn_user' => 'warning',
                                                'suspend_user' => 'danger',
                                                'dismiss_flag','dismiss_report' => 'secondary',
                                                'escalate' => 'primary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}">
                                            {{ ucwords(str_replace('_', ' ', $action->action_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($action->target_type ?? '-') }} #{{ $action->target_id ?? '-' }}</td>
                                    <td class="text-truncate text-secondary" style="max-width: 200px;">{{ $action->reason }}</td>
                                    <td>
                                        @if($action->ai_flag_source)
                                            <span class="badge bg-primary-subtle text-primary border" style="font-size:0.7rem;"><i class="bi bi-robot me-1"></i>AI</span>
                                        @elseif($action->report_id)
                                            <span class="badge bg-warning-subtle text-warning border" style="font-size:0.7rem;"><i class="bi bi-flag me-1"></i>Report</span>
                                        @else
                                            <span class="badge bg-light text-secondary border" style="font-size:0.7rem;">Manual</span>
                                        @endif
                                    </td>
                                    <td class="text-muted">{{ $action->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                    No moderation actions taken yet.
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i> Moderator Guidelines</h5>
            <div class="d-flex flex-column gap-2 small">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                    <span class="text-secondary">Review AI flagged content fairly and impartially</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                    <span class="text-secondary">Always provide a clear reason when removing content</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                    <span class="text-secondary">Warn users before suspending when possible</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                    <span class="text-secondary">Dismiss AI flags that are clearly false positives</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check2-circle text-success mt-1 flex-shrink-0"></i>
                    <span class="text-secondary">Escalate serious violations to the admin team</span>
                </div>
                <hr class="opacity-25 my-1">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-x-circle text-danger mt-1 flex-shrink-0"></i>
                    <span class="text-secondary">You cannot permanently delete user accounts</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-x-circle text-danger mt-1 flex-shrink-0"></i>
                    <span class="text-secondary">You cannot access analytics or system settings</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
