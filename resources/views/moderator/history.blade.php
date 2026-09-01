@extends('layouts.moderator')
@section('title', 'Action History - DiscussHub Moderator')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">My Action History</h2>
        <p class="text-secondary small mb-0">{{ $actions->total() }} total moderation actions recorded under your account.</p>
    </div>
</div>

<div class="dg-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Reason</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                @forelse($actions as $action)
                    <tr>
                        <td class="text-muted">{{ $action->created_at->format('M d, Y H:i') }}</td>
                        <td>
                            @php
                                $color = match($action->action_type) {
                                    'remove_question','remove_answer' => 'danger',
                                    'warn_user' => 'warning',
                                    'suspend_user' => 'danger',
                                    'dismiss_flag','dismiss_report' => 'secondary',
                                    'escalate' => 'primary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}">
                                {{ ucwords(str_replace('_', ' ', $action->action_type)) }}
                            </span>
                        </td>
                        <td>{{ ucfirst($action->target_type ?? '-') }} #{{ $action->target_id ?? '-' }}</td>
                        <td class="text-truncate text-secondary" style="max-width: 280px;">{{ $action->reason }}</td>
                        <td>
                            @if($action->ai_flag_source)
                                <span class="badge bg-primary-subtle text-primary border" style="font-size:0.7rem;"><i class="bi bi-robot me-1"></i>AI Flag</span>
                            @elseif($action->report_id)
                                <span class="badge bg-warning-subtle text-warning border" style="font-size:0.7rem;"><i class="bi bi-flag me-1"></i>User Report</span>
                            @else
                                <span class="badge bg-light text-secondary border" style="font-size:0.7rem;">Manual</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-clock-history fs-2 d-block mb-2 opacity-50"></i>
                            No moderation actions recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $actions->links() }}
</div>
@endsection
