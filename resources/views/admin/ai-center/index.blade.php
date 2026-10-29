@extends('layouts.admin')

@section('title', 'Intelligence Center & Telemetry - DiscussHub Admin')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h2 class="fw-bold mb-0" style="color: var(--admin-text-primary); font-size: 1.5rem; letter-spacing: -0.3px;">Intelligence Center & Telemetry</h2>
        <p class="small text-muted mb-0 mt-1">Local machine learning health, request latency statistics, and automated moderation queue</p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <!-- Engine Server Status Badge -->
        @if($ollamaAvailable)
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a; box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.25);"></span>
                <span>Engine Status: Online & Ready</span>
            </span>
        @else
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #2563eb; box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.25);"></span>
                <span>Engine Status: Active (Built-in)</span>
            </span>
        @endif

        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center">
            <i class="bi bi-speedometer2 fs-6 me-2"></i>
            <span>Dashboard</span>
        </a>
    </div>
</div>

<!-- AI Telemetry Cards (Sopan Pro Architecture) -->
<div class="row g-3 mb-4">
    <!-- Requests Today -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card-pro">
            <div class="stat-icon-pro blue">
                <i class="bi bi-lightning-charge-fill"></i>
            </div>
            <div class="stat-info-pro">
                <div class="label">Requests Today</div>
                <div class="value">{{ number_format($stats['total_today']) }}</div>
                <div class="stat-trend-pill primary">
                    <i class="bi bi-calendar-event"></i> {{ number_format($stats['total_week']) }} this week
                </div>
            </div>
        </div>
    </div>

    <!-- Total Invocations -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card-pro">
            <div class="stat-icon-pro indigo">
                <i class="bi bi-cpu-fill"></i>
            </div>
            <div class="stat-info-pro">
                <div class="label">Total Invocations</div>
                <div class="value">{{ number_format($stats['total_all']) }}</div>
                <div class="stat-trend-pill success">
                    <i class="bi bi-infinity"></i> All-time lifetime calls
                </div>
            </div>
        </div>
    </div>

    <!-- Average Response Latency -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card-pro">
            <div class="stat-icon-pro yellow">
                <i class="bi bi-stopwatch-fill"></i>
            </div>
            <div class="stat-info-pro">
                <div class="label">Average Response Latency</div>
                <div class="value">{{ $stats['avg_response_time'] }}s</div>
                <div class="stat-trend-pill muted">
                    <i class="bi bi-hdd-network text-primary"></i> Local processing
                </div>
            </div>
        </div>
    </div>

    <!-- Success Rate -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card-pro">
            <div class="stat-icon-pro green">
                <i class="bi bi-shield-check"></i>
            </div>
            <div class="stat-info-pro">
                <div class="label">Success Rate</div>
                <div class="value text-success">{{ $stats['success_rate'] }}%</div>
                <div class="stat-trend-pill success">
                    <i class="bi bi-check-circle"></i> Zero unhandled exceptions
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Capability Breakdown & Moderation Queue -->
<div class="row g-4 mb-4">
    <!-- Invocations by Capability Chart -->
    <div class="col-lg-6">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon blue"><i class="bi bi-bar-chart-fill"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Invocations by Capability</h5>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                    {{ count($featureLabels) }} Capabilities
                </span>
            </div>
            <div class="p-3">
                <div style="height: 260px;">
                    <canvas id="aiFeaturesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Automated Moderation Queue -->
    <div class="col-lg-6">
        <div class="dg-card overflow-hidden h-100">
            <div class="dash-card-header">
                <div class="d-flex align-items-center gap-2">
                    <div class="dash-card-icon red"><i class="bi bi-shield-exclamation"></i></div>
                    <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Automated Moderation Queue</h5>
                </div>
                <span class="badge {{ ($flaggedQuestions->count() + $flaggedAnswers->count()) > 0 ? 'bg-danger-subtle text-danger border border-danger-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} rounded-pill px-2.5 py-1 small fw-semibold">
                    {{ $flaggedQuestions->count() + $flaggedAnswers->count() }} Flagged
                </span>
            </div>
            <div class="p-3">
                @if($flaggedQuestions->isNotEmpty() || $flaggedAnswers->isNotEmpty())
                    <div class="d-flex flex-column gap-2.5" style="max-height: 270px; overflow-y: auto;">
                        @foreach($flaggedQuestions as $fq)
                            <div class="queue-item">
                                <div class="queue-icon-box danger">
                                    <i class="bi bi-question-circle-fill"></i>
                                </div>
                                <div class="queue-body">
                                    <div class="queue-meta-row">
                                        <div class="queue-badges">
                                            <span class="queue-reason-badge">FLAGGED</span>
                                            <span class="queue-target-badge"><i class="bi bi-question-circle me-1"></i>Question #{{ $fq->id }}</span>
                                        </div>
                                        <span class="queue-time"><i class="bi bi-clock"></i> {{ $fq->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="queue-snippet">
                                        <a href="{{ route('admin.questions.show', $fq) }}" class="fw-semibold text-decoration-none" style="color: var(--admin-text-primary);">
                                            "{{ Str::limit($fq->title, 65) }}"
                                        </a>
                                    </div>
                                    <div class="queue-footer">
                                        <div class="d-flex align-items-center gap-1">
                                            <span>Author:</span>
                                            <strong style="color: var(--admin-text-primary);">{{ $fq->user->user_name ?? 'Anonymous' }}</strong>
                                        </div>
                                        <form action="{{ route('admin.ai-center.clearQuestionFlag', $fq) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 py-0.5 shadow-none fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                <i class="bi bi-check2"></i>
                                                <span>Clear Flag</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @foreach($flaggedAnswers as $fa)
                            <div class="queue-item">
                                <div class="queue-icon-box warning">
                                    <i class="bi bi-chat-dots-fill"></i>
                                </div>
                                <div class="queue-body">
                                    <div class="queue-meta-row">
                                        <div class="queue-badges">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">FLAGGED</span>
                                            <span class="queue-target-badge"><i class="bi bi-chat-dots me-1"></i>Answer #{{ $fa->id }}</span>
                                        </div>
                                        <span class="queue-time"><i class="bi bi-clock"></i> {{ $fa->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="queue-snippet">
                                        <span>"{{ Str::limit(strip_tags($fa->answer), 65) }}"</span>
                                    </div>
                                    <div class="queue-footer">
                                        <div class="d-flex align-items-center gap-1">
                                            <span>Author:</span>
                                            <strong style="color: var(--admin-text-primary);">{{ $fa->user->user_name ?? 'Anonymous' }}</strong>
                                        </div>
                                        <form action="{{ route('admin.ai-center.clearAnswerFlag', $fa) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 py-0.5 shadow-none fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                <i class="bi bi-check2"></i>
                                                <span>Clear Flag</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4 text-muted">
                        <div class="stat-icon-wrap green mx-auto mb-3" style="width: 48px; height: 48px; font-size: 22px; border-radius: 50%;">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color: var(--admin-text-primary);">Moderation Queue is Clean</h6>
                        <p class="text-muted small mb-0">No flagged items in queue. Automated safety filters are active.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Tag Groups (AI Smart Merge) -->
<div class="dg-card overflow-hidden mb-4">
    <div class="dash-card-header">
        <div class="d-flex align-items-center gap-2">
            <div class="dash-card-icon yellow"><i class="bi bi-bezier2"></i></div>
            <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">AI Duplicate Tag Groups</h5>
        </div>
        <button class="btn btn-sm btn-primary text-white rounded-pill px-3 py-1 shadow-none fw-medium d-inline-flex align-items-center" id="btn-refresh-groups">
            <span id="groups-spinner" class="spinner-border spinner-border-sm d-none me-2"></span>
            <i class="bi bi-arrow-repeat me-1.5"></i>
            <span>Scan for Duplicates</span>
        </button>
    </div>
    <div class="p-4" id="duplicate-groups-container">
        <div class="text-center py-3 text-muted">
            <i class="bi bi-bezier2 fs-2 d-block mb-2 text-muted opacity-50"></i>
            <p class="small mb-0">Click <strong>"Scan for Duplicates"</strong> to find similar tag clusters that can be merged.</p>
        </div>
    </div>
</div>

<!-- Recent Telemetry Log Table -->
<div class="dg-card overflow-hidden">
    <div class="dash-card-header">
        <div class="d-flex align-items-center gap-2">
            <div class="dash-card-icon blue"><i class="bi bi-list-columns-reverse"></i></div>
            <h5 class="fw-bold mb-0" style="font-size: 15px; color: var(--admin-text-primary);">Recent Telemetry Logs</h5>
        </div>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 fw-semibold">
            {{ count($recentRequests) }} Logs
        </span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle small mb-0">
            <thead>
                <tr>
                    <th class="ps-4">Timestamp</th>
                    <th>Feature</th>
                    <th>Input Size</th>
                    <th>Response Time</th>
                    <th>Status</th>
                    <th class="pe-4">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRequests as $req)
                    <tr>
                        <td class="ps-4">
                            <span class="text-muted d-inline-flex align-items-center gap-1.5">
                                <i class="bi bi-clock"></i>
                                <span>{{ $req->created_at->format('H:i:s') }}</span>
                                <small class="text-muted opacity-75">· {{ $req->created_at->format('M d') }}</small>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                {{ ucfirst(str_replace('_', ' ', $req->feature)) }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ number_format($req->input_length ?? 0) }}</span>
                            <span class="text-muted" style="font-size: 11px;">chars</span>
                        </td>
                        <td>
                            @if($req->response_time < 5)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 fw-semibold">
                                    <i class="bi bi-lightning-charge me-1"></i>{{ $req->response_time }}s
                                </span>
                            @elseif($req->response_time < 15)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5 fw-semibold">
                                    <i class="bi bi-stopwatch me-1"></i>{{ $req->response_time }}s
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-0.5 fw-semibold">
                                    <i class="bi bi-hourglass-split me-1"></i>{{ $req->response_time }}s
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($req->success)
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i>Success
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 fw-bold">
                                    <i class="bi bi-x-circle-fill me-1"></i>Failed
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 text-muted small">
                            {{ $req->details ?? 'Local Execution' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-cpu fs-2 d-block mb-2 text-muted opacity-50"></i>
                            <div class="fw-semibold">No telemetry requests logged yet</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('aiFeaturesChart')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($featureLabels) !!},
                    datasets: [{
                        label: 'Requests',
                        data: {!! json_encode($featureCounts) !!},
                        backgroundColor: '#2563eb',
                        hoverBackgroundColor: '#1d4ed8',
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1e293b',
                            padding: 10,
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
                            ticks: { font: { family: 'Inter', size: 11, weight: '500' } },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Duplicate Tag Groups
        document.getElementById('btn-refresh-groups')?.addEventListener('click', async () => {
            const spinner = document.getElementById('groups-spinner');
            const container = document.getElementById('duplicate-groups-container');
            spinner?.classList.remove('d-none');

            try {
                const res = await fetch('{{ route("admin.admin.api.tags.groups") }}', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const groups = data.groups || [];

                if (groups.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <div class="stat-icon-wrap green mx-auto mb-3" style="width: 48px; height: 48px; font-size: 22px; border-radius: 50%;">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="color: var(--admin-text-primary);">No Duplicates Detected</h6>
                            <p class="text-muted small mb-0">All discussion tags appear to be unique and well-structured.</p>
                        </div>`;
                    return;
                }

                let html = '<div class="d-flex flex-column gap-3">';
                groups.forEach((group, idx) => {
                    const tagPills = group.tags.map(t => `<span class="topic-pill me-1 mb-1"><i class="bi bi-hash"></i>${t.name} <small class="text-muted ms-1">(${t.usage_count})</small></span>`).join('');
                    const options = group.tags.map(t => `<option value="${t.id}">#${t.name}</option>`).join('');
                    const mergeIds = group.tags.map(t => t.id);

                    html += `
                    <div class="p-3 rounded-3 border" style="background: var(--admin-canvas-bg);" id="group-${idx}">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2.5 py-1 fw-bold">${group.max_similarity}% similarity</span>
                            <span class="small text-muted">${group.tags.length} similar tags found</span>
                        </div>
                        <div class="mb-3 d-flex flex-wrap">${tagPills}</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label class="small fw-semibold mb-0" style="color: var(--admin-text-primary);">Keep Canonical Tag:</label>
                            <select class="form-select form-select-sm" style="max-width:200px; border-radius: 8px;" id="canonical-${idx}">${options}</select>
                            <button type="button" class="btn btn-sm btn-primary text-white rounded-pill px-3 py-1 shadow-none fw-medium"
                                onclick="mergeTagGroup(${idx}, [${mergeIds}])">
                                <i class="bi bi-arrow-down-up me-1"></i> Merge Group
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 shadow-none fw-medium"
                                onclick="document.getElementById('group-${idx}').remove(); showToast('Group dismissed.', 'info');">
                                Dismiss
                            </button>
                        </div>
                    </div>`;
                });
                html += '</div>';

                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = '<p class="text-danger small mb-0"><i class="bi bi-exclamation-circle me-1"></i>Failed to load duplicate tag groups.</p>';
            } finally {
                spinner?.classList.add('d-none');
            }
        });
    });

    async function mergeTagGroup(idx, allIds) {
        const canonicalId = parseInt(document.getElementById(`canonical-${idx}`).value);
        const mergeIds = allIds.filter(id => id !== canonicalId);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        try {
            const res = await fetch('{{ route("admin.admin.api.tags.merge") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ canonical_id: canonicalId, merge_ids: mergeIds })
            });
            const data = await res.json();
            if (data.canonical_tag) {
                document.getElementById(`group-${idx}`).remove();
                if (typeof showToast === 'function') {
                    showToast(`Merged into #${data.canonical_tag.name}. ${data.questions_updated} questions updated.`, 'success');
                }
            }
        } catch (e) {
            if (typeof showToast === 'function') {
                showToast('Merge failed. Please try again.', 'danger');
            }
        }
    }
</script>
@endpush
@endsection
