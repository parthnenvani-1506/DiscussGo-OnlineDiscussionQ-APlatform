@extends('layouts.admin')
@section('title', 'Badge Management - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Badge Management</h2>
        <p class="text-secondary small mb-0">Create and manage community achievement badges.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Create Badge Form -->
    <div class="col-lg-4">
        <div class="dg-card p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle text-primary me-2"></i> Create New Badge</h5>
            <form action="{{ route('admin.badges.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Badge Name</label>
                    <input type="text" name="name" class="form-control form-control-dg @error('name') is-invalid @enderror"
                        value="{{ old('name') }}" required placeholder="e.g. Speed Answerer">
                    @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea name="description" rows="2" class="form-control form-control-dg @error('description') is-invalid @enderror"
                        required placeholder="Short description of how to earn this badge">{{ old('description') }}</textarea>
                    @error('description') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Bootstrap Icon Class</label>
                    <input type="text" name="icon" class="form-control form-control-dg @error('icon') is-invalid @enderror"
                        value="{{ old('icon', 'bi bi-award') }}" required placeholder="e.g. bi bi-lightning-charge">
                    @error('icon') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tier</label>
                    <select name="tier" class="form-select form-control-dg" required>
                        <option value="bronze" {{ old('tier') === 'bronze' ? 'selected' : '' }}>🥉 Bronze</option>
                        <option value="silver" {{ old('tier') === 'silver' ? 'selected' : '' }}>🥈 Silver</option>
                        <option value="gold"   {{ old('tier') === 'gold'   ? 'selected' : '' }}>🥇 Gold</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Criteria Key</label>
                    <input type="text" name="criteria" class="form-control form-control-dg @error('criteria') is-invalid @enderror"
                        value="{{ old('criteria') }}" required placeholder="e.g. speed_answerer">
                    <div class="form-text small text-muted">Unique identifier used in BadgeService. Snake_case.</div>
                    @error('criteria') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary rounded-pill px-4 w-100">
                    <i class="bi bi-plus-lg me-1"></i> Create Badge
                </button>
            </form>
        </div>
    </div>

    <!-- Badges List -->
    <div class="col-lg-8">
        <div class="dg-card overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th>Badge</th>
                            <th>Tier</th>
                            <th>Criteria Key</th>
                            <th>Awarded To</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($badges as $badge)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="p-2 rounded-circle bg-warning-subtle text-warning fs-5">
                                            <i class="{{ $badge->icon }}"></i>
                                        </span>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $badge->name }}</div>
                                            <div class="text-muted" style="font-size:0.75rem;">{{ $badge->description }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $tierColor = match($badge->tier) {
                                            'gold'   => 'warning',
                                            'silver' => 'secondary',
                                            'bronze' => 'danger',
                                            default  => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $tierColor }}-subtle text-{{ $tierColor }} border border-{{ $tierColor }}">
                                        {{ ucfirst($badge->tier) }}
                                    </span>
                                </td>
                                <td><code>{{ $badge->criteria }}</code></td>
                                <td>
                                    <span class="badge bg-light text-secondary border">{{ $badge->users_count }} users</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Inline Edit -->
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            data-bs-toggle="modal" data-bs-target="#editBadgeModal"
                                            data-id="{{ $badge->id }}"
                                            data-name="{{ $badge->name }}"
                                            data-description="{{ $badge->description }}"
                                            data-icon="{{ $badge->icon }}"
                                            data-tier="{{ $badge->tier }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <form action="{{ route('admin.badges.destroy', $badge) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Delete this badge? All awards will be removed.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">No badges created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Badge Modal -->
<div class="modal fade" id="editBadgeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" id="editBadgeForm" class="modal-content border-0 shadow">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil text-primary me-2"></i> Edit Badge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Badge Name</label>
                    <input type="text" name="name" id="edit-badge-name" class="form-control form-control-dg" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Description</label>
                    <textarea name="description" id="edit-badge-description" rows="2" class="form-control form-control-dg" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Icon Class</label>
                    <input type="text" name="icon" id="edit-badge-icon" class="form-control form-control-dg" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Tier</label>
                    <select name="tier" id="edit-badge-tier" class="form-select form-control-dg" required>
                        <option value="bronze">🥉 Bronze</option>
                        <option value="silver">🥈 Silver</option>
                        <option value="gold">🥇 Gold</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('editBadgeModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', e => {
            const btn = e.relatedTarget;
            document.getElementById('editBadgeForm').action = `/admin/badges/${btn.dataset.id}`;
            document.getElementById('edit-badge-name').value = btn.dataset.name;
            document.getElementById('edit-badge-description').value = btn.dataset.description;
            document.getElementById('edit-badge-icon').value = btn.dataset.icon;
            document.getElementById('edit-badge-tier').value = btn.dataset.tier;
        });
    }
});
</script>
@endpush
@endsection
