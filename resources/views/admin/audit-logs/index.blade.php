@extends('layouts.admin')

@section('title', 'Audit Trail - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Administrative Audit Logs</h2>
        <p class="text-secondary small mb-0">Immutable record of administrative actions, moderation decisions, and system events</p>
    </div>
</div>

<div class="dg-card p-3 mb-4">
    <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-6">
            <select name="action" class="form-select form-control-dg" onchange="this.form.submit()">
                <option value="">All Action Types</option>
                @foreach($distinctActions as $act)
                    <option value="{{ $act }}" {{ $action === $act ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $act)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <select name="admin_id" class="form-select form-control-dg" onchange="this.form.submit()">
                <option value="">All Administrators</option>
                @foreach($admins as $adm)
                    <option value="{{ $adm->id }}" {{ $adminId == $adm->id ? 'selected' : '' }}>{{ $adm->name ?? $adm->user_name }} ({{ $adm->email }})</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<div class="dg-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Administrator</th>
                    <th>Action</th>
                    <th>Event Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-muted">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td>
                            <strong>{{ $log->admin->username ?? 'System' }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ strtoupper(str_replace('_', ' ', $log->action)) }}</span>
                        </td>
                        <td class="text-secondary">{{ $log->details }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-4 text-muted">No audit logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $logs->links() }}
</div>
@endsection
