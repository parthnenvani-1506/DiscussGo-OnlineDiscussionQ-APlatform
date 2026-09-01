@extends('layouts.moderator')
@section('title', 'User Reports Queue - DiscussHub Moderator')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">User Reports Queue</h2>
        <p class="text-secondary small mb-0">{{ $reports->total() }} pending reports submitted by community members awaiting your review.</p>
    </div>
</div>

<!-- Reports Feed -->
<div class="d-flex flex-column gap-3">
    @forelse($reports as $report)
        <div class="dg-card p-4 border-start border-4 {{ in_array($report->reason, ['offensive','harassment']) ? 'border-danger' : 'border-warning' }}">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <span class="badge bg-danger-subtle text-danger text-uppercase fw-bold">{{ $report->reason }}</span>
                    <span class="badge bg-light text-secondary border ms-1">{{ strtoupper($report->reportable_type) }} #{{ $report->reportable_id }}</span>
                    <span class="badge bg-warning text-dark ms-1">Pending</span>
                </div>
                <div class="small text-muted">
                    Reported by <strong>{{ $report->reporter->user_name ?? 'Unknown' }}</strong> · {{ $report->created_at->diffForHumans() }}
                </div>
            </div>

            @if($report->details)
                <div class="p-2 rounded bg-light border small text-secondary mb-3">
                    <strong>User note:</strong> {{ $report->details }}
                </div>
            @endif

            <!-- Content Preview -->
            <div class="p-3 rounded bg-body border mb-3">
                @if($report->reportable)
                    @if($report->reportable_type === 'question')
                        <h6 class="fw-bold mb-1 text-dark">{{ $report->reportable->title }}</h6>
                        <div class="small text-secondary">{{ Str::limit(strip_tags($report->reportable->description), 200) }}</div>
                        <div class="mt-2 small text-muted">Author: <strong>{{ $report->reportable->user->user_name ?? 'Unknown' }}</strong></div>
                    @else
                        <div class="small text-secondary">{{ Str::limit(strip_tags($report->reportable->answer), 200) }}</div>
                        <div class="mt-2 small text-muted">Author: <strong>{{ $report->reportable->user->user_name ?? 'Unknown' }}</strong></div>
                    @endif
                @else
                    <div class="text-muted fst-italic small">The reported content has already been removed.</div>
                @endif
            </div>

            <!-- Action Controls -->
            <div class="d-flex align-items-center gap-2 pt-2 border-top flex-wrap">
                @if($report->reportable)
                    @php $reportableUser = $report->reportable->user ?? null; @endphp

                    <!-- Remove Content -->
                    <button type="button" class="btn btn-sm btn-danger"
                        data-bs-toggle="modal" data-bs-target="#reportRemoveModal"
                        data-report-id="{{ $report->id }}"
                        data-type="{{ $report->reportable_type }}"
                        data-content-id="{{ $report->reportable->id }}">
                        <i class="bi bi-trash me-1"></i> Remove Content
                    </button>

                    @if($reportableUser)
                        <!-- Warn Author -->
                        <button type="button" class="btn btn-sm btn-warning"
                            data-bs-toggle="modal" data-bs-target="#warnUserModal"
                            data-user-id="{{ $reportableUser->id }}"
                            data-user-name="{{ $reportableUser->user_name }}">
                            <i class="bi bi-exclamation-triangle me-1"></i> Warn Author
                        </button>

                        <!-- Suspend Author -->
                        <button type="button" class="btn btn-sm btn-outline-danger"
                            data-bs-toggle="modal" data-bs-target="#suspendUserModal"
                            data-user-id="{{ $reportableUser->id }}"
                            data-user-name="{{ $reportableUser->user_name }}">
                            <i class="bi bi-slash-circle me-1"></i> Suspend Author
                        </button>
                    @endif
                @endif

                <!-- Dismiss Report -->
                <form action="{{ route('moderator.dismiss-report', $report) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Dismiss Report
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="dg-card p-5 text-center">
            <i class="bi bi-shield-check text-success display-4"></i>
            <h5 class="mt-3 text-secondary">No pending reports</h5>
            <p class="small text-muted mb-0">The community is clean. All reports have been reviewed.</p>
        </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">{{ $reports->links() }}</div>

<!-- Remove Content Modal -->
<div class="modal fade" id="reportRemoveModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="reportRemoveForm" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-trash text-danger me-2"></i> Remove Reported Content</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary mb-3">The content author will be notified. Reason is mandatory and will be recorded.</p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Removal Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control form-control-dg" rows="3" required placeholder="e.g. Content violates community guidelines — spam/offensive material..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                    <i class="bi bi-trash me-1"></i> Remove & Resolve
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Warn User Modal -->
<div class="modal fade" id="warnUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="warnUserForm" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-exclamation-triangle text-warning me-2"></i> Issue Warning</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary mb-3">Sending official warning to <strong id="warnUserName"></strong>. They will be notified via the platform.</p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Warning Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control form-control-dg" rows="3" required placeholder="e.g. Multiple community guideline violations..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-4">
                    <i class="bi bi-exclamation-triangle me-1"></i> Send Warning
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Suspend User Modal -->
<div class="modal fade" id="suspendUserModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="suspendUserForm" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-slash-circle text-danger me-2"></i> Suspend Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small text-secondary mb-3">Suspending <strong id="suspendUserName"></strong>. Choose duration and provide reason.</p>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Suspension Duration <span class="text-danger">*</span></label>
                    <select name="days" class="form-select form-control-dg" required>
                        <option value="1">1 Day</option>
                        <option value="3">3 Days</option>
                        <option value="7" selected>7 Days</option>
                        <option value="30">30 Days</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control form-control-dg" rows="3" required placeholder="e.g. Repeated community guideline violations..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-4">
                    <i class="bi bi-slash-circle me-1"></i> Suspend Account
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const removeModal = document.getElementById('reportRemoveModal');
    if (removeModal) {
        removeModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            const type = btn.dataset.type;
            const id = btn.dataset.contentId;
            document.getElementById('reportRemoveForm').action =
                type === 'question' ? `/moderator/remove-question/${id}` : `/moderator/remove-answer/${id}`;
        });
    }
    const warnModal = document.getElementById('warnUserModal');
    if (warnModal) {
        warnModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            document.getElementById('warnUserForm').action = `/moderator/warn-user/${btn.dataset.userId}`;
            document.getElementById('warnUserName').textContent = btn.dataset.userName;
        });
    }
    const suspendModal = document.getElementById('suspendUserModal');
    if (suspendModal) {
        suspendModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            document.getElementById('suspendUserForm').action = `/moderator/suspend-user/${btn.dataset.userId}`;
            document.getElementById('suspendUserName').textContent = btn.dataset.userName;
        });
    }
});
</script>
@endpush
@endsection
