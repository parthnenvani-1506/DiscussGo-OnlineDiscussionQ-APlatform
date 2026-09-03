@extends('layouts.admin')

@section('title', 'Manage Users - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">User Management</h2>
        <p class="text-secondary small mb-0">{{ $users->total() }} total registered accounts</p>
    </div>
</div>

<!-- Filters Bar -->
<div class="dg-card p-3 mb-4">
    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text bg-transparent"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control form-control-dg" placeholder="Search by username, email, city..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-4">
            <select name="status" class="form-select form-control-dg" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended Only</option>
            </select>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="dg-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>City</th>
                    <th>Reputation</th>
                    <th>Questions / Answers</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="small">
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if($user->profile_image)
                                    <img src="{{ asset('profiles/' . $user->profile_image) }}" class="rounded-circle object-fit-cover" width="30" height="30" alt="avatar">
                                @else
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                        {{ strtoupper(substr($user->user_name, 0, 1)) }}
                                    </div>
                                @endif
                                <a href="{{ route('admin.users.show', $user) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $user->user_name }}
                                </a>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->city ?? '-' }}</td>
                        <td>
                            <span class="reputation-badge py-0 px-2 rep-badge-fmt"
                                data-rep="{{ $user->reputation }}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="{{ number_format($user->reputation) }} reputation points">
                                <i class="bi bi-stars"></i> <span class="rep-value">{{ $user->reputation }}</span>
                            </span>
                        </td>
                        <td>{{ $user->questions_count }} Qs / {{ $user->answers_count }} As</td>
                        <td>
                            @if($user->is_suspended)
                                <span class="badge bg-danger">Suspended</span>
                            @elseif($user->role === 'moderator')
                                <span class="badge bg-warning text-dark"><i class="bi bi-shield-half me-1"></i>Moderator</span>
                            @else
                                <span class="badge bg-success">Active</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary" title="View details">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $user->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }}" title="{{ $user->is_suspended ? 'Unsuspend' : 'Suspend' }}">
                                        <i class="bi {{ $user->is_suspended ? 'bi-check-circle' : 'bi-slash-circle' }}"></i>
                                    </button>
                                </form>

                                @if($user->role === 'moderator')
                                    <form action="{{ route('admin.users.remove-moderator', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Remove moderator role">
                                            <i class="bi bi-shield-x"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.users.appoint-moderator', $user) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" title="Appoint as moderator">
                                            <i class="bi bi-shield-plus"></i>
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete user">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No users found matching search.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $users->links() }}
</div>
@endsection
