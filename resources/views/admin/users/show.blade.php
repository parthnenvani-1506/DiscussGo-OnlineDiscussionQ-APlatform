@extends('layouts.admin')

@section('title', 'User Details: ' . $user->user_name . ' - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">User Inspection: {{ $user->user_name }}</h2>
        <p class="text-secondary small mb-0">ID #{{ $user->id }} · {{ $user->email }}</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="dg-card p-4 text-center">
            @if($user->profile_image)
                <img src="{{ asset('profiles/' . $user->profile_image) }}" class="rounded-circle object-fit-cover shadow-sm mb-3" width="90" height="90" alt="avatar">
            @else
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm mx-auto mb-3" style="width: 90px; height: 90px; font-size: 2rem;">
                    {{ strtoupper(substr($user->user_name, 0, 1)) }}
                </div>
            @endif

            <h4 class="fw-bold text-dark mb-1">{{ $user->user_name }}</h4>
            <span class="badge bg-primary mb-3">{{ ucfirst($user->level ?? 'Newcomer') }}</span>

            <div class="d-flex justify-content-center gap-2 mb-3">
                <span class="reputation-badge rep-badge-fmt"
                    data-rep="{{ $user->reputation }}"
                    data-bs-toggle="tooltip"
                    data-bs-placement="top"
                    title="{{ number_format($user->reputation) }} reputation points">
                    <i class="bi bi-stars"></i> <span class="rep-value">{{ $user->reputation }}</span> Rep
                </span>
                <span class="badge {{ $user->is_suspended ? 'bg-danger' : 'bg-success' }}">
                    {{ $user->is_suspended ? 'Suspended' : 'Active' }}
                </span>
            </div>

            <div class="text-secondary small text-start border-top pt-3">
                <div class="mb-1"><strong>Email:</strong> {{ $user->email }}</div>
                <div class="mb-1"><strong>City:</strong> {{ $user->city ?? 'Not specified' }}</div>
                <div class="mb-1"><strong>Joined:</strong> {{ $user->created_at->format('M d, Y') }}</div>
                @if($user->is_suspended)
                    <div class="mt-2 text-danger small">
                        <strong>Suspension Reason:</strong> {{ $user->suspended_reason }}
                    </div>
                @endif
            </div>

            <form action="{{ route('admin.users.suspend', $user) }}" method="POST" class="mt-4">
                @csrf
                @if(!$user->is_suspended)
                    <input type="text" name="reason" class="form-control form-control-sm mb-2" placeholder="Reason for suspension...">
                @endif
                <button type="submit" class="btn btn-sm w-100 {{ $user->is_suspended ? 'btn-success' : 'btn-warning' }}">
                    {{ $user->is_suspended ? 'Unsuspend Account' : 'Suspend Account' }}
                </button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="dg-card p-4">
            <h5 class="fw-bold text-dark mb-3">User Questions ({{ $questions->total() }})</h5>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-hover align-middle small">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Likes</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $q)
                            <tr>
                                <td><a href="{{ route('admin.questions.show', $q) }}" class="text-dark fw-semibold">{{ $q->title }}</a></td>
                                <td><span class="badge bg-light text-secondary border">{{ $q->category->name }}</span></td>
                                <td>{{ $q->vote_score }}</td>
                                <td>{{ $q->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-2">No questions posted.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $questions->links() }}

            <h5 class="fw-bold text-dark mb-3 mt-4">User Answers ({{ $answers->total() }})</h5>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle small">
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Preview</th>
                            <th>Likes</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($answers as $a)
                            <tr>
                                <td><a href="{{ route('admin.questions.show', $a->question) }}" class="text-dark fw-semibold">{{ $a->question->title }}</a></td>
                                <td class="text-truncate" style="max-width: 200px;">{{ Str::limit(strip_tags($a->answer), 60) }}</td>
                                <td>{{ $a->vote_score }}</td>
                                <td>{{ $a->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-muted text-center py-2">No answers posted.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $answers->links() }}
        </div>
    </div>
</div>
@endsection
