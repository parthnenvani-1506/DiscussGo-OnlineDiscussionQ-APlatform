@extends('layouts.admin')
@section('title', 'Moderator Management - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Moderator Management</h2>
        <p class="text-secondary small mb-0">Appoint and manage community moderators. Moderators can review flags and reports.</p>
    </div>
</div>

<div class="row g-4">
    <!-- Current Moderators -->
    <div class="col-lg-8">
        <div class="dg-card p-4 mb-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-half text-warning me-2"></i> Current Moderators ({{ $moderators->count() }})</h5>
            @if($moderators->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Reputation</th>
                                <th>Q / A</th>
                                <th>Joined</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($moderators as $mod)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($mod->profile_image && $mod->profile_image !== 'default_profile.png')
                                                <img src="{{ asset('profiles/' . $mod->profile_image) }}" class="rounded-circle object-fit-cover" width="30" height="30" alt="avatar">
                                            @else
                                                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold" style="width:30px;height:30px;font-size:0.75rem;">
                                                    {{ strtoupper(substr($mod->user_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $mod->user_name }}</div>
                                                <div class="text-muted" style="font-size:0.75rem;">{{ $mod->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="reputation-badge py-0 px-2"><i class="bi bi-stars"></i> {{ $mod->reputation }}</span></td>
                                    <td>{{ $mod->questions_count }} Q / {{ $mod->answers_count }} A</td>
                                    <td class="text-muted">{{ $mod->created_at->format('M Y') }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.users.remove-moderator', $mod) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Remove moderator role from {{ $mod->user_name }}?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-person-dash me-1"></i> Remove Role
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-shield-half fs-3 d-block mb-2"></i>
                    No moderators appointed yet. Appoint users from the Users management page.
                </div>
            @endif
        </div>

        <!-- Recent Moderation Actions -->
        <div class="dg-card p-4">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-clock-history text-primary me-2"></i> Recent Moderation Actions</h5>
            @if($recentActions->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Moderator</th>
                                <th>Action</th>
                                <th>Target</th>
                                <th>Reason</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentActions as $action)
                                <tr>
                                    <td class="fw-semibold">{{ $action->moderator->user_name ?? 'Unknown' }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ ucwords(str_replace('_', ' ', $action->action_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($action->target_type ?? '-') }} #{{ $action->target_id ?? '-' }}</td>
                                    <td class="text-truncate text-secondary" style="max-width:200px;">{{ $action->reason }}</td>
                                    <td class="text-muted">{{ $action->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted small text-center py-3 mb-0">No moderation actions recorded yet.</p>
            @endif
        </div>
    </div>

    <!-- Appoint New Moderator -->
    <div class="col-lg-4">
        <div class="dg-card p-4 sticky-top" style="top:80px;">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-plus text-success me-2"></i> Appoint Moderator</h5>
            <p class="text-secondary small mb-3">Go to the Users page, find a trusted user and click "Appoint as Moderator".</p>
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary rounded-pill px-4 w-100">
                <i class="bi bi-people me-2"></i> Browse Users
            </a>

            <hr class="my-4">

            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-info-circle text-primary me-2"></i> Moderator Permissions</h6>
            <ul class="list-unstyled small text-secondary d-flex flex-column gap-1">
                <li><i class="bi bi-check2 text-success me-2"></i> Review AI flagged content</li>
                <li><i class="bi bi-check2 text-success me-2"></i> Remove questions and answers</li>
                <li><i class="bi bi-check2 text-success me-2"></i> Warn users</li>
                <li><i class="bi bi-check2 text-success me-2"></i> Temporarily suspend users</li>
                <li><i class="bi bi-check2 text-success me-2"></i> Review user reports</li>
                <li><i class="bi bi-x text-danger me-2"></i> Cannot access analytics</li>
                <li><i class="bi bi-x text-danger me-2"></i> Cannot delete users permanently</li>
                <li><i class="bi bi-x text-danger me-2"></i> Cannot manage categories/tags/badges</li>
            </ul>
        </div>
    </div>
</div>
@endsection
