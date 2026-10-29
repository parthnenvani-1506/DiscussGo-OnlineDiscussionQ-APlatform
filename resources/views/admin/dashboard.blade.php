@extends('layouts.admin')

@section('title', 'Admin Dashboard - DiscussHub')

@section('content')
<!-- Page Header & Summary -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="color: var(--admin-text-primary); font-size: 1.5rem; letter-spacing: -0.3px;">System Dashboard</h2>
        <p class="small text-muted mb-0 mt-1">Real-time telemetry, platform health, and content moderation status</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.ai-center.index') }}" class="btn btn-sm btn-primary text-white rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
            <i class="bi bi-cpu fs-6 me-2"></i>
            <span>AI Center</span>
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm {{ $kpis['pending_reports'] > 0 ? 'btn-danger' : 'btn-outline-danger' }} rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
            <i class="bi bi-flag fs-6 me-2"></i>
            <span>Reports ({{ $kpis['pending_reports'] }})</span>
        </a>
    </div>
</div>

<!-- KPI Stat Cards Grid (Sopan Style with DiscussHub Palette) -->
<div class="row g-3 mb-4">
    <!-- Total Users -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index') }}" class="stat-card-link">
            <div class="stat-card-pro">
                <div class="stat-icon-pro blue">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-info-pro">
                    <div class="label">Total Users</div>
                    <div class="value">{{ number_format($kpis['total_users']) }}</div>
                    <div class="stat-trend-pill success">
                        <i class="bi bi-arrow-up-right"></i> +{{ $kpis['new_users_week'] }} this week
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Questions Asked -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.questions.index') }}" class="stat-card-link">
            <div class="stat-card-pro">
                <div class="stat-icon-pro indigo">
                    <i class="bi bi-patch-question-fill"></i>
                </div>
                <div class="stat-info-pro">
                    <div class="label">Questions Asked</div>
                    <div class="value">{{ number_format($kpis['total_questions']) }}</div>
                    <div class="stat-trend-pill primary">
                        <i class="bi bi-arrow-up-right"></i> +{{ $kpis['new_questions_week'] }} this week
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Community Answers -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.answers.index') }}" class="stat-card-link">
            <div class="stat-card-pro">
                <div class="stat-icon-pro green">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
                <div class="stat-info-pro">
                    <div class="label">Community Answers</div>
                    <div class="value">{{ number_format($kpis['total_answers']) }}</div>
                    <div class="stat-trend-pill success">
                        <i class="bi bi-arrow-up-right"></i> +{{ $kpis['new_answers_week'] }} this week
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Pending Reports -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.reports.index') }}" class="stat-card-link">
            <div class="stat-card-pro">
                <div class="stat-icon-pro {{ $kpis['pending_reports'] > 0 ? 'red' : 'green' }}">
                    <i class="bi {{ $kpis['pending_reports'] > 0 ? 'bi-flag-fill' : 'bi-shield-check' }}"></i>
                </div>
                <div class="stat-info-pro">
                    <div class="label">Pending Reports</div>
                    <div class="value {{ $kpis['pending_reports'] > 0 ? 'text-danger' : '' }}">{{ $kpis['pending_reports'] }}</div>
                    @if($kpis['pending_reports'] > 0)
                        <div class="stat-trend-pill danger">
                            <i class="bi bi-exclamation-circle-fill"></i> Requires attention
                        </div>
                    @else
                        <div class="stat-trend-pill success">
                            <i class="bi bi-check-circle-fill"></i> All queues clear
                        </div>
                    @endif
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Chart Graphs Row -->
<div class="row g-4 mb-4">
    <!-- 7-Day Activity Velocity Line Chart -->
    <div class="col-lg-8">
        <div class="dg-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon blue"><i class="bi bi-activity"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Activity Velocity (Past 7 Days)</h5>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-semibold">
                    Daily Pulse
                </span>
            </div>
            <div style="height: 290px;">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Distribution Doughnut Chart -->
    <div class="col-lg-4">
        <div class="dg-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon indigo"><i class="bi bi-pie-chart-fill"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Category Share</h5>
                </div>
                <a href="{{ route('admin.categories.index') }}" class="dash-card-link">
                    <span>Manage</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div style="height: 270px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Queues & Tables Row -->
<div class="row g-4">
    <!-- Recent Questions -->
    <div class="col-lg-7">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon blue"><i class="bi bi-chat-square-text"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Recent Questions</h5>
                </div>
                <a href="{{ route('admin.questions.index') }}" class="dash-card-link">
                    <span>View all</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentQuestions as $q)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('admin.questions.show', $q) }}" class="fw-semibold text-decoration-none text-truncate d-inline-block" style="max-width: 240px; color: var(--admin-text-primary);" title="{{ $q->title }}">
                                        {{ $q->title }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="user-avatar-tiny">
                                            {{ strtoupper(substr($q->user->user_name, 0, 1)) }}
                                        </div>
                                        <span class="fw-medium">{{ $q->user->user_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="topic-pill">{{ $q->category->name }}</span>
                                </td>
                                <td>
                                    <span class="text-muted"><i class="bi bi-clock me-1"></i>{{ $q->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.questions.show', $q) }}" class="action-btn-sm" title="Review Question">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-chat-square-text fs-2 d-block mb-2 text-muted opacity-50"></i>
                                    No recent questions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Moderation Reports Queue -->
    <div class="col-lg-5">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon red"><i class="bi bi-shield-exclamation"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Pending Reports Queue</h5>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="dash-card-link danger">
                    <span>Open Queue ({{ $kpis['pending_reports'] }})</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="p-3">
                @if($pendingReports->isNotEmpty())
                    <div class="d-flex flex-column gap-2.5">
                        @foreach($pendingReports as $report)
                            <div class="queue-item">
                                <div class="queue-icon-box danger">
                                    <i class="bi bi-flag-fill"></i>
                                </div>
                                <div class="queue-body">
                                    <div class="queue-meta-row">
                                        <div class="queue-badges">
                                            <span class="queue-reason-badge">
                                                {{ strtoupper($report->reason) }}
                                            </span>
                                            <span class="queue-target-badge">
                                                <i class="bi {{ strtolower($report->reportable_type) === 'question' ? 'bi-question-circle' : 'bi-chat-dots' }} me-1"></i>{{ ucfirst($report->reportable_type) }} #{{ $report->reportable_id }}
                                            </span>
                                        </div>
                                        <span class="queue-time">
                                            <i class="bi bi-clock"></i> {{ $report->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    <div class="queue-snippet">
                                        @if($report->reportable)
                                            @if(strtolower($report->reportable_type) === 'question')
                                                <span class="fw-semibold">"{{ Str::limit($report->reportable->title, 65) }}"</span>
                                            @else
                                                <span>"{{ Str::limit(strip_tags($report->reportable->answer), 65) }}"</span>
                                            @endif
                                        @else
                                            <span class="text-muted fst-italic small">
                                                <i class="bi bi-info-circle me-1 text-secondary"></i>Reported content pending review or already archived
                                            </span>
                                        @endif
                                    </div>

                                    <div class="queue-footer">
                                        <div class="d-flex align-items-center gap-1">
                                            <span>Reported by</span>
                                            <strong style="color: var(--admin-text-primary);">{{ $report->reporter->user_name ?? 'Anonymous' }}</strong>
                                        </div>
                                        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-0.5 shadow-none fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                            <span>Review</span>
                                            <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted">
                        <div class="stat-icon-wrap green mx-auto mb-3" style="width: 52px; height: 52px; font-size: 24px; border-radius: 50%;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: var(--admin-text-primary);">Moderation Queue is Clean</h6>
                        <p class="text-muted small mb-0">No pending reports require review right now.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Activity Velocity Chart
        const velocityCtx = document.getElementById('velocityChart')?.getContext('2d');
        if (velocityCtx) {
            new Chart(velocityCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($dates) !!},
                    datasets: [
                        {
                            label: 'Questions',
                            data: {!! json_encode($questionCounts) !!},
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Answers',
                            data: {!! json_encode($answerCounts) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'New Users',
                            data: {!! json_encode($userCounts) !!},
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { family: 'Inter', size: 12, weight: '500' }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            grid: { color: 'rgba(226, 232, 240, 0.5)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart')?.getContext('2d');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($categoryLabels) !!},
                    datasets: [{
                        data: {!! json_encode($categoryData) !!},
                        backgroundColor: ['#2563eb', '#10b981', '#ff7a00', '#6366f1', '#ef4444'],
                        borderWidth: 3,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { family: 'Inter', size: 11, weight: '500' }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
    });
</script>
@endpush
@endsection
