@extends('layouts.admin')

@section('title', 'Intelligence Control Center & Telemetry - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-cpu text-primary me-2"></i> Intelligence Center & Telemetry
        </h2>
        <p class="text-secondary small mb-0">Local machine learning health, request latency statistics, and automated moderation queue</p>
    </div>

    <!-- Engine Server Status Badge -->
    <div class="d-flex align-items-center gap-2 p-2 px-3 rounded-pill border {{ $ollamaAvailable ? 'bg-success-subtle border-success text-success' : 'bg-primary-subtle border-primary text-primary' }}">
        <span class="rounded-circle {{ $ollamaAvailable ? 'bg-success' : 'bg-primary' }}" style="width: 10px; height: 10px;"></span>
        <span class="small fw-bold">Engine Status: {{ $ollamaAvailable ? 'Online & Ready' : 'Active (Built-in Engine)' }}</span>
    </div>
</div>

<!-- AI Telemetry Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Requests Today</div>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total_today']) }}</h3>
            <span class="text-muted small">{{ number_format($stats['total_week']) }} this week</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Total Invocations</div>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($stats['total_all']) }}</h3>
            <span class="text-muted small">All-time lifetime calls</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Average Response Latency</div>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['avg_response_time'] }}s</h3>
            <span class="text-success small fw-semibold">Local processing</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="dg-card p-3">
            <div class="text-secondary small fw-medium">Success Rate</div>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $stats['success_rate'] }}%</h3>
            <span class="text-muted small">Zero unhandled exceptions</span>
        </div>
    </div>
</div>

<!-- Feature Breakdown Graph & Flagged Content -->
<div class="row g-4 mb-4">
    <!-- Chart Column -->
    <div class="col-lg-6">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-bar-chart-fill text-primary me-2"></i> Invocations by Capability</h5>
            <div style="height: 250px;">
                <canvas id="aiFeaturesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Moderation Flagged Content Review Queue -->
    <div class="col-lg-6">
        <div class="dg-card p-4 h-100">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-exclamation text-danger me-2"></i> Automated Moderation Queue</h5>
            @if($flaggedQuestions->isNotEmpty() || $flaggedAnswers->isNotEmpty())
                <div class="d-flex flex-column gap-2" style="max-height: 260px; overflow-y: auto;">
                    @foreach($flaggedQuestions as $fq)
                        <div class="p-2 rounded bg-light border d-flex align-items-center justify-content-between">
                            <div class="text-truncate me-2" style="max-width: 280px;">
                                <span class="badge bg-danger">Question</span>
                                <a href="{{ route('admin.questions.show', $fq) }}" class="small fw-semibold text-dark text-decoration-none ms-1">{{ $fq->title }}</a>
                            </div>
                            <form action="{{ route('admin.ai-center.clearQuestionFlag', $fq) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 small">Clear Flag</button>
                            </form>
                        </div>
                    @endforeach

                    @foreach($flaggedAnswers as $fa)
                        <div class="p-2 rounded bg-light border d-flex align-items-center justify-content-between">
                            <div class="text-truncate me-2" style="max-width: 280px;">
                                <span class="badge bg-warning text-dark">Answer</span>
                                <span class="small text-secondary ms-1">{{ Str::limit(strip_tags($fa->answer), 40) }}</span>
                            </div>
                            <form action="{{ route('admin.ai-center.clearAnswerFlag', $fa) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2 small">Clear Flag</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-shield-check text-success fs-2 d-block mb-1"></i>
                    No flagged items currently in queue. Automated safety filters active.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Duplicate Tag Groups (AI Smart Merge) -->
<div class="dg-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-bezier2 text-warning me-2"></i> AI Duplicate Tag Groups</h5>
        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" id="btn-refresh-groups">
            <span id="groups-spinner" class="spinner-border spinner-border-sm d-none me-1"></span>
            <i class="bi bi-arrow-repeat me-1"></i> Scan for Duplicates
        </button>
    </div>
    <div id="duplicate-groups-container">
        <p class="text-muted small mb-0">Click "Scan for Duplicates" to find similar tag clusters that can be merged.</p>
    </div>
</div>

<!-- Recent Telemetry Log Table -->
<div class="dg-card p-4">
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-list-columns-reverse text-primary me-2"></i> Recent Telemetry Logs</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Feature</th>
                    <th>Input Size</th>
                    <th>Response Time</th>
                    <th>Status</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRequests as $req)
                    <tr>
                        <td class="text-muted">{{ $req->created_at->format('H:i:s M d') }}</td>
                        <td><span class="badge bg-primary-subtle text-primary border">{{ ucfirst(str_replace('_', ' ', $req->feature)) }}</span></td>
                        <td>{{ $req->input_length ?? '-' }} chars</td>
                        <td><strong>{{ $req->response_time }}s</strong></td>
                        <td>
                            @if($req->success)
                                <span class="badge bg-success">Success</span>
                            @else
                                <span class="badge bg-danger">Failed</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $req->details ?? 'Local Execution' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No telemetry requests logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('aiFeaturesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($featureLabels) !!},
                datasets: [{
                    label: 'Requests',
                    data: {!! json_encode($featureCounts) !!},
                    backgroundColor: '#2563eb',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });

        // Duplicate Tag Groups
        document.getElementById('btn-refresh-groups').addEventListener('click', async () => {
            const spinner = document.getElementById('groups-spinner');
            const container = document.getElementById('duplicate-groups-container');
            spinner.classList.remove('d-none');

            try {
                const res = await fetch('{{ route("admin.admin.api.tags.groups") }}', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const groups = data.groups || [];

                if (groups.length === 0) {
                    container.innerHTML = '<div class="text-center py-3 text-muted small"><i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>No duplicate tag groups detected.</div>';
                    return;
                }

                let html = '';
                groups.forEach((group, idx) => {
                    const tagPills = group.tags.map(t => `<span class="tag-badge me-1">#${t.name} <small class="opacity-75">(${t.usage_count})</small></span>`).join('');
                    const options = group.tags.map(t => `<option value="${t.id}">#${t.name}</option>`).join('');
                    const mergeIds = group.tags.map(t => t.id);

                    html += `
                    <div class="p-3 rounded border mb-3" id="group-${idx}">
                        <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                            <span class="badge bg-warning text-dark">${group.max_similarity}% similarity</span>
                            <span class="small text-muted">${group.tags.length} similar tags</span>
                        </div>
                        <div class="mb-3">${tagPills}</div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <label class="small fw-semibold mb-0">Keep:</label>
                            <select class="form-select form-select-sm form-control-dg" style="max-width:200px;" id="canonical-${idx}">${options}</select>
                            <button type="button" class="btn btn-sm btn-warning rounded-pill px-3"
                                onclick="mergeTagGroup(${idx}, [${mergeIds}])">
                                <i class="bi bi-arrow-down-up me-1"></i> Merge Group
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3"
                                onclick="document.getElementById('group-${idx}').remove(); showToast('Group dismissed.', 'info');">
                                Dismiss
                            </button>
                        </div>
                    </div>`;
                });

                container.innerHTML = html;
            } catch (e) {
                container.innerHTML = '<p class="text-danger small">Failed to load duplicate groups.</p>';
            } finally {
                spinner.classList.add('d-none');
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
                showToast(`Merged into #${data.canonical_tag.name}. ${data.questions_updated} questions updated.`, 'success');
            }
        } catch (e) {
            showToast('Merge failed. Please try again.', 'danger');
        }
    }
</script>
@endpush
@endsection
