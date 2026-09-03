@extends('layouts.admin')

@section('title', 'Platform Analytics & Growth - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Platform Analytics & Growth</h2>
        <p class="text-secondary small mb-0">Long-term platform health, question resolution rate, and contributor leaderboards</p>
    </div>
</div>

<!-- High Level Metrics -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Solution Resolution Rate</div>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $acceptanceRate }}%</h3>
            <span class="text-muted small">{{ $answeredQuestions }} / {{ $totalQuestions }} resolved</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Answers Per Question</div>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $avgAnswersPerQuestion }}</h3>
            <span class="text-muted small">{{ $totalAnswers }} total answers</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Total Community Likes</div>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalVotes) }}</h3>
            <span class="text-muted small">Engagement signals</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Registered Developers</div>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalUsers) }}</h3>
            <span class="text-muted small">Active community members</span>
        </div>
    </div>
</div>

<!-- 30-Day Growth Chart -->
<div class="dg-card p-4 mb-4">
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-graph-up text-primary me-2"></i> 30-Day Content Growth & Engagement</h5>
    <div style="height: 300px;">
        <canvas id="monthlyGrowthChart"></canvas>
    </div>
</div>

<!-- Top Tags & Top Contributors -->
<div class="row g-4">
    <!-- Top Tags -->
    <div class="col-lg-6">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-tags-fill text-primary me-2"></i> Top Discussion Tags</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Tag</th>
                            <th>Discussions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topTags as $index => $t)
                            <tr>
                                <td>#{{ $index + 1 }}</td>
                                <td><span class="tag-badge">#{{ $t->name }}</span></td>
                                <td><strong>{{ $t->usage_count }}</strong> questions</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Contributors -->
    <div class="col-lg-6">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-trophy-fill text-warning me-2"></i> Top Community Contributors</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Contributor</th>
                            <th>Reputation</th>
                            <th>Questions</th>
                            <th>Answers</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topContributors as $contributor)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <a href="{{ route('admin.users.show', $contributor) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $contributor->user_name }}
                                        </a>
                                        <span class="badge bg-light text-secondary border">{{ $contributor->level ?? 'Novice' }}</span>
                                    </div>
                                </td>
                                <td><span class="reputation-badge rep-badge-fmt"
                                    data-rep="{{ $contributor->reputation }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ number_format($contributor->reputation) }} reputation points">
                                    <i class="bi bi-stars"></i> <span class="rep-value">{{ $contributor->reputation }}</span>
                                </span></td>
                                <td>{{ $contributor->questions_count }}</td>
                                <td>{{ $contributor->answers_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const monthlyCtx = document.getElementById('monthlyGrowthChart').getContext('2d');
        new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyDates) !!},
                datasets: [
                    {
                        label: 'Questions',
                        data: {!! json_encode($monthlyQuestions) !!},
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Answers',
                        data: {!! json_encode($monthlyAnswers) !!},
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
    });
</script>
@endpush
@endsection
