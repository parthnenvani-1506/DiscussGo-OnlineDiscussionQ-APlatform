@extends('layouts.admin')

@section('title', 'Admin Dashboard - DiscussHub')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-1" style="color: var(--admin-text-primary);">System Dashboard</h2>
        <p class="small mb-0" style="color: var(--admin-text-secondary);">Real-time telemetry, platform health, and content moderation status</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.ai-center.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none fw-medium d-inline-flex align-items-center gap-1.5">
            <i class="bi bi-cpu text-primary"></i> AI Center
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm {{ $kpis['pending_reports'] > 0 ? 'btn-danger' : 'btn-outline-secondary' }} rounded-pill px-3 shadow-none fw-medium d-inline-flex align-items-center gap-1.5">
            <i class="bi bi-flag"></i> Reports ({{ $kpis['pending_reports'] }})
        </a>
    </div>
</div>

<!-- KPI Stat Cards Grid (Sopan Style with DiscussHub Palette) -->
<div class="row g-3 mb-4">
    <!-- Total Users -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap blue">
                <i class="bi bi-people-fill"></i>
            </div>
            <div class="stat-info">
                <div class="label">Total Users</div>
                <div class="value">{{ number_format($kpis['total_users']) }}</div>
                <div class="trend text-success">
                    <i class="bi bi-arrow-up-right"></i> +{{ $kpis['new_users_week'] }} this week
                </div>
            </div>
        </div>
    </div>

    <!-- Questions Asked -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap indigo">
                <i class="bi bi-patch-question-fill"></i>
            </div>
            <div class="stat-info">
                <div class="label">Questions Asked</div>
                <div class="value">{{ number_format($kpis['total_questions']) }}</div>
                <div class="trend text-primary">
                    <i class="bi bi-arrow-up-right"></i> +{{ $kpis['new_questions_week'] }} this week
                </div>
            </div>
        </div>
    </div>

    <!-- Community Answers -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap green">
                <i class="bi bi-chat-dots-fill"></i>
            </div>
            <div class="stat-info">
                <div class="label">Community Answers</div>
                <div class="value">{{ number_format($kpis['total_answers']) }}</div>
                <div class="trend text-success">
                    <i class="bi bi-arrow-up-right"></i> +{{ $kpis['new_answers_week'] }} this week
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Reports -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon-wrap {{ $kpis['pending_reports'] > 0 ? 'red' : 'green' }}">
                <i class="bi {{ $kpis['pending_reports'] > 0 ? 'bi-flag-fill' : 'bi-shield-check' }}"></i>
            </div>
            <div class="stat-info">
                <div class="label">Pending Reports</div>
                <div class="value {{ $kpis['pending_reports'] > 0 ? 'text-danger' : '' }}">{{ $kpis['pending_reports'] }}</div>
                <div class="trend {{ $kpis['pending_reports'] > 0 ? 'text-danger' : 'text-muted' }}">
                    <i class="bi {{ $kpis['pending_reports'] > 0 ? 'bi-exclamation-circle' : 'bi-check-circle' }}"></i>
                    {{ $kpis['pending_reports'] > 0 ? 'Requires attention' : 'All queues clear' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Graphs Row -->
<div class="row g-4 mb-4">
    <!-- 7-Day Activity Trends Line Chart -->
    <div class="col-lg-8">
        <div class="dg-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="dash-card-title">
                    <i class="bi bi-activity text-primary fs-5"></i>
                    <span>Activity Velocity (Past 7 Days)</span>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 small fw-semibold">Daily Pulse</span>
            </div>
            <div style="height: 280px;">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Distribution Chart -->
    <div class="col-lg-4">
        <div class="dg-card p-4 h-100">
            <div class="dash-card-title mb-3">
                <i class="bi bi-pie-chart-fill text-primary fs-5"></i>
                <span>Category Share</span>
            </div>
            <div style="height: 250px; position: relative;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Queues & Tables Row -->
<div class="row g-4">
    <!-- Recent Questions -->
    <div class="col-lg-6">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <i class="bi bi-question-circle text-primary"></i>
                    <span>Recent Questions</span>
                </div>
                <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none fw-medium">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentQuestions as $q)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.questions.show', $q) }}" class="fw-semibold text-decoration-none text-truncate d-inline-block" style="max-width: 200px; color: var(--admin-text-primary);">
                                        {{ $q->title }}
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-medium">{{ $q->user->user_name }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1">{{ $q->category->name }}</span>
                                </td>
                                <td class="text-muted">{{ $q->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No recent questions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Moderation Reports -->
    <div class="col-lg-6">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="dash-card-title">
                    <i class="bi bi-shield-exclamation text-danger"></i>
                    <span>Pending Reports Queue</span>
                </div>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 shadow-none fw-medium">Open Queue</a>
            </div>
            <div class="p-3">
                @if($pendingReports->isNotEmpty())
                    <div class="d-flex flex-column gap-2">
                        @foreach($pendingReports as $report)
                            <div class="p-3 rounded-3 border d-flex align-items-center justify-content-between" style="background: var(--admin-icon-bg); border-color: var(--admin-border) !important;">
                                <div>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-uppercase mb-1 rounded-pill">{{ $report->reason }}</span>
                                    <div class="small fw-semibold mt-1" style="color: var(--admin-text-primary);">
                                        Report on {{ class_basename($report->reportable_type) }} #{{ $report->reportable_id }} by {{ $report->reporter->user_name }}
                                    </div>
                                    <div class="text-muted" style="font-size: 11px;">{{ $report->created_at->diffForHumans() }}</div>
                                </div>
                                <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-danger rounded-pill px-3">Review</a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 text-muted small">
                        <div class="p-3 rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                            <i class="bi bi-shield-check fs-3"></i>
                        </div>
                        <div class="fw-semibold text-dark">No pending reports!</div>
                        <p class="text-muted mb-0">Community moderation queue is completely clean.</p>
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
                            borderWidth: 2,
                            pointRadius: 3
                        },
                        {
                            label: 'Answers',
                            data: {!! json_encode($answerCounts) !!},
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3
                        },
                        {
                            label: 'New Users',
                            data: {!! json_encode($userCounts) !!},
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2,
                            pointRadius: 3
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
                        backgroundColor: ['#2563eb', '#16a34a', '#ff7a00', '#4f46e5', '#dc2626'],
                        borderWidth: 2,
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
                                font: { family: 'Inter', size: 11 }
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
