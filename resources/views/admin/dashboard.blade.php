@extends('layouts.admin')

@section('title', 'Admin Dashboard - DiscussHub')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">System Dashboard</h2>
        <p class="text-secondary small mb-0">Platform health, live telemetry, and content moderation queues</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.ai-center.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="bi bi-robot me-1"></i> AI Center
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
            <i class="bi bi-flag me-1"></i> Reports ({{ $kpis['pending_reports'] }})
        </a>
    </div>
</div>

<!-- KPI Cards Grid -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">Total Users</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($kpis['total_users']) }}</h3>
                    <span class="text-success small fw-semibold">+{{ $kpis['new_users_week'] }} this week</span>
                </div>
                <div class="p-3 rounded bg-primary-subtle text-primary fs-3">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">Questions Asked</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($kpis['total_questions']) }}</h3>
                    <span class="text-primary small fw-semibold">+{{ $kpis['new_questions_week'] }} this week</span>
                </div>
                <div class="p-3 rounded bg-info-subtle text-info fs-3">
                    <i class="bi bi-patch-question-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">Community Answers</div>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($kpis['total_answers']) }}</h3>
                    <span class="text-success small fw-semibold">+{{ $kpis['new_answers_week'] }} this week</span>
                </div>
                <div class="p-3 rounded bg-success-subtle text-success fs-3">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-secondary small fw-medium">Pending Reports</div>
                    <h3 class="fw-bold {{ $kpis['pending_reports'] > 0 ? 'text-danger' : 'text-dark' }} mb-0 mt-1">{{ $kpis['pending_reports'] }}</h3>
                    <span class="text-muted small">Requires review</span>
                </div>
                <div class="p-3 rounded bg-danger-subtle text-danger fs-3">
                    <i class="bi bi-flag-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Graphs Row -->
<div class="row g-4 mb-4">
    <!-- 7-Day Activity Trends Line Chart -->
    <div class="col-lg-8">
        <div class="dg-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-activity text-primary me-2"></i> Activity Velocity (Past 7 Days)</h5>
                <span class="badge bg-light text-secondary border">Daily Pulse</span>
            </div>
            <div style="height: 280px;">
                <canvas id="velocityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Category Distribution Chart -->
    <div class="col-lg-4">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart-fill text-primary me-2"></i> Category Share</h5>
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
        <div class="dg-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0">Recent Questions</h5>
                <a href="{{ route('admin.questions.index') }}" class="small text-primary text-decoration-none">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentQuestions as $q)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.questions.show', $q) }}" class="fw-semibold text-dark text-decoration-none text-truncate d-inline-block" style="max-width: 220px;">
                                        {{ $q->title }}
                                    </a>
                                </td>
                                <td>{{ $q->user->user_name }}</td>
                                <td><span class="badge bg-light text-secondary border">{{ $q->category->name }}</span></td>
                                <td class="text-muted">{{ $q->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pending Moderation Reports -->
    <div class="col-lg-6">
        <div class="dg-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0">Pending Reports Queue</h5>
                <a href="{{ route('admin.reports.index') }}" class="small text-danger text-decoration-none">Open Queue</a>
            </div>
            @if($pendingReports->isNotEmpty())
                <div class="d-flex flex-column gap-2">
                    @foreach($pendingReports as $report)
                        <div class="p-3 rounded bg-light border d-flex align-items-center justify-content-between">
                            <div>
                                <span class="badge bg-danger-subtle text-danger text-uppercase mb-1">{{ $report->reason }}</span>
                                <div class="small fw-semibold text-dark">
                                    Report on {{ $report->reportable_type }} #{{ $report->reportable_id }} by {{ $report->reporter->user_name }}
                                </div>
                            </div>
                            <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-danger">Review</a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                    No pending moderation reports! All clean.
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Activity Velocity Chart
        const velocityCtx = document.getElementById('velocityChart').getContext('2d');
        new Chart(velocityCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dates) !!},
                datasets: [
                    {
                        label: 'New Questions',
                        data: {!! json_encode($questionCounts) !!},
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'New Answers',
                        data: {!! json_encode($answerCounts) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'New Users',
                        data: {!! json_encode($userCounts) !!},
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categoryLabels) !!},
                datasets: [{
                    data: {!! json_encode($categoryData) !!},
                    backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    });
</script>
@endpush
@endsection
