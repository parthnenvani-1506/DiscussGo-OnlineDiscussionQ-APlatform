@extends('layouts.admin')

@section('title', 'Platform Analytics & Growth - DiscussHub Admin')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="color: var(--admin-text-primary); font-size: 1.5rem; letter-spacing: -0.3px;">Platform Analytics & Growth</h2>
        <p class="small text-muted mb-0 mt-1">Long-term platform health, question resolution rate, and contributor leaderboards</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
            <i class="bi bi-speedometer2 fs-6 me-2"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.tags.index') }}" class="btn btn-sm btn-primary text-white rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
            <i class="bi bi-tags fs-6 me-2"></i>
            <span>Manage Tags</span>
        </a>
    </div>
</div>

<!-- High Level Metrics (Sopan Pro Stat Cards) -->
<div class="row g-3 mb-4">
    <!-- Solution Resolution Rate -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card-pro">
            <div class="stat-icon-pro green">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="stat-info-pro">
                <div class="label">Solution Resolution Rate</div>
                <div class="value text-success">{{ $acceptanceRate }}%</div>
                <div class="stat-trend-pill success">
                    <i class="bi bi-check2"></i> {{ $answeredQuestions }} / {{ $totalQuestions }} resolved
                </div>
            </div>
        </div>
    </div>

    <!-- Answers Per Question -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card-pro">
            <div class="stat-icon-pro blue">
                <i class="bi bi-chat-quote-fill"></i>
            </div>
            <div class="stat-info-pro">
                <div class="label">Answers Per Question</div>
                <div class="value">{{ $avgAnswersPerQuestion }}</div>
                <div class="stat-trend-pill primary">
                    <i class="bi bi-chat-dots"></i> {{ number_format($totalAnswers) }} total answers
                </div>
            </div>
        </div>
    </div>

    <!-- Total Community Likes -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card-pro">
            <div class="stat-icon-pro yellow">
                <i class="bi bi-hand-thumbs-up-fill"></i>
            </div>
            <div class="stat-info-pro">
                <div class="label">Total Community Likes</div>
                <div class="value">{{ number_format($totalVotes) }}</div>
                <div class="stat-trend-pill muted">
                    <i class="bi bi-lightning-charge-fill text-warning"></i> Engagement signals
                </div>
            </div>
        </div>
    </div>

    <!-- Registered Developers -->
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.users.index') }}" class="stat-card-link">
            <div class="stat-card-pro">
                <div class="stat-icon-pro indigo">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="stat-info-pro">
                    <div class="label">Registered Developers</div>
                    <div class="value">{{ number_format($totalUsers) }}</div>
                    <div class="stat-trend-pill primary">
                        <i class="bi bi-person-check"></i> Active community members
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- 30-Day Growth Chart -->
<div class="dg-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="dash-card-icon blue"><i class="bi bi-graph-up-arrow"></i></div>
            <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">30-Day Content Growth & Engagement</h5>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-semibold">
            Monthly Overview
        </span>
    </div>
    <div style="height: 300px;">
        <canvas id="monthlyGrowthChart"></canvas>
    </div>
</div>

<!-- Top Tags & Top Contributors -->
<div class="row g-4">
    <!-- Top Discussion Tags -->
    <div class="col-lg-6">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon indigo"><i class="bi bi-tags-fill"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Top Discussion Tags</h5>
                </div>
                <a href="{{ route('admin.tags.index') }}" class="dash-card-link">
                    <span>Manage tags</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4" style="width: 75px;">Rank</th>
                            <th>Tag</th>
                            <th>Discussions</th>
                            <th class="text-end pe-4" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topTags as $index => $t)
                            <tr>
                                <td class="ps-4">
                                    @if($index === 0)
                                        <span class="rank-badge rank-1"><i class="bi bi-trophy-fill me-1"></i>#1</span>
                                    @elseif($index === 1)
                                        <span class="rank-badge rank-2">#2</span>
                                    @elseif($index === 2)
                                        <span class="rank-badge rank-3">#3</span>
                                    @else
                                        <span class="rank-badge rank-other">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.questions.index', ['tag' => $t->name]) }}" class="topic-pill text-decoration-none">
                                        <i class="bi bi-hash"></i>{{ $t->name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 fw-semibold">
                                        {{ $t->usage_count }} {{ Str::plural('question', $t->usage_count) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.questions.index', ['tag' => $t->name]) }}" class="action-btn-sm" title="View Questions for #{{ $t->name }}">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-tags fs-2 d-block mb-2 text-muted opacity-50"></i>
                                    <div class="fw-semibold">No tags created yet</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Community Contributors -->
    <div class="col-lg-6">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon yellow"><i class="bi bi-trophy-fill"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Top Community Contributors</h5>
                </div>
                <a href="{{ route('admin.users.index') }}" class="dash-card-link">
                    <span>All users</span> <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle small mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Contributor</th>
                            <th>Reputation</th>
                            <th>Questions</th>
                            <th>Answers</th>
                            <th class="text-end pe-4" style="width: 70px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topContributors as $index => $contributor)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($index === 0)
                                            <span class="rank-badge rank-1" title="Rank 1"><i class="bi bi-trophy-fill"></i></span>
                                        @elseif($index === 1)
                                            <span class="rank-badge rank-2" title="Rank 2">#2</span>
                                        @elseif($index === 2)
                                            <span class="rank-badge rank-3" title="Rank 3">#3</span>
                                        @else
                                            <span class="rank-badge rank-other" title="Rank {{ $index + 1 }}">#{{ $index + 1 }}</span>
                                        @endif

                                        <div class="user-avatar-tiny">
                                            {{ strtoupper(substr($contributor->user_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.users.show', $contributor) }}" class="fw-semibold text-decoration-none d-block" style="color: var(--admin-text-primary); line-height: 1.2;">
                                                {{ $contributor->user_name }}
                                            </a>
                                            <span class="badge bg-light text-secondary border rounded-pill mt-0.5" style="font-size: 9.5px; padding: 1px 6px;">
                                                {{ ucfirst($contributor->level ?? 'novice') }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="reputation-badge rep-badge-fmt"
                                        data-rep="{{ $contributor->reputation }}"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="{{ number_format($contributor->reputation) }} reputation points">
                                        <i class="bi bi-stars"></i> <span class="rep-value">{{ $contributor->reputation }}</span>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold" style="color: var(--admin-text-primary);">{{ $contributor->questions_count }}</span>
                                    <span class="text-muted" style="font-size: 11px;"> Qs</span>
                                </td>
                                <td>
                                    <span class="fw-semibold" style="color: var(--admin-text-primary);">{{ $contributor->answers_count }}</span>
                                    <span class="text-muted" style="font-size: 11px;"> Ans</span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('admin.users.show', $contributor) }}" class="action-btn-sm" title="View {{ $contributor->user_name }}'s Profile">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="bi bi-people fs-2 d-block mb-2 text-muted opacity-50"></i>
                                    <div class="fw-semibold">No active contributors found</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const monthlyCtx = document.getElementById('monthlyGrowthChart')?.getContext('2d');
        if (monthlyCtx) {
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyDates) !!},
                    datasets: [
                        {
                            label: 'Questions',
                            data: {!! json_encode($monthlyQuestions) !!},
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 2.5,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Answers',
                            data: {!! json_encode($monthlyAnswers) !!},
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.08)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointRadius: 2.5,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                font: { family: 'Inter', size: 12, weight: '500' }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { family: 'Inter', size: 12, weight: '600' },
                            bodyFont: { family: 'Inter', size: 12 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0, font: { family: 'Inter', size: 11 } },
                            grid: { color: 'rgba(0, 0, 0, 0.04)' }
                        },
                        x: {
                            ticks: {
                                font: { family: 'Inter', size: 10 },
                                maxRotation: 0,
                                autoSkip: true,
                                maxTicksLimit: 10
                            },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
