@extends('layouts.admin')

@section('title', 'Moderation Reports - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Moderation Queue</h2>
        <p class="text-secondary small mb-0">Review user-flagged content for spam, toxicity, or community guidelines breaches</p>
    </div>
</div>

<!-- Status Tabs -->
<div class="d-flex gap-2 mb-4">
    <a href="{{ route('admin.reports.index', ['status' => 'pending']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'pending' ? 'btn-danger' : 'btn-outline-secondary' }}">
        Pending Review
    </a>
    <a href="{{ route('admin.reports.index', ['status' => 'resolved']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'resolved' ? 'btn-success' : 'btn-outline-secondary' }}">
        Resolved
    </a>
    <a href="{{ route('admin.reports.index', ['status' => 'dismissed']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'dismissed' ? 'btn-secondary' : 'btn-outline-secondary' }}">
        Dismissed
    </a>
    <a href="{{ route('admin.reports.index', ['status' => 'all']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
        All Reports
    </a>
</div>

<!-- Reports Feed -->
<div class="d-flex flex-column gap-3">
    @forelse($reports as $report)
        <div class="dg-card p-4 border-start border-4 {{ $report->status === 'pending' ? 'border-danger' : 'border-secondary' }}">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <span class="badge bg-danger-subtle text-danger text-uppercase fw-bold">{{ $report->reason }}</span>
                    <span class="badge bg-light text-secondary border ms-1">{{ strtoupper($report->reportable_type) }} #{{ $report->reportable_id }}</span>
                    <span class="badge {{ $report->status === 'pending' ? 'bg-warning text-dark' : ($report->status === 'resolved' ? 'bg-success' : 'bg-secondary') }} ms-1">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>
                <div class="small text-muted">
                    Reported by <strong>{{ $report->reporter->user_name }}</strong> · {{ $report->created_at->diffForHumans() }}
                </div>
            </div>

            @if($report->details)
                <div class="p-2 rounded bg-light border small text-secondary mb-3">
                    <strong>User Comment:</strong> {{ $report->details }}
                </div>
            @endif

            <!-- Content Preview -->
            <div class="p-3 rounded bg-body border mb-3">
                @if($report->reportable)
                    @if($report->reportable_type === 'question')
                        <h6 class="fw-bold mb-1 text-dark">{{ $report->reportable->title }}</h6>
                        <div class="small text-secondary">{{ Str::limit(strip_tags($report->reportable->description), 200) }}</div>
                        <div class="mt-2 small text-muted">Author: <strong>{{ $report->reportable->user->user_name }}</strong></div>
                    @else
                        <div class="small text-secondary">{!! nl2br(e($report->reportable->answer)) !!}</div>
                        <div class="mt-2 small text-muted">Author: <strong>{{ $report->reportable->user->user_name }}</strong></div>
                    @endif
                @else
                    <div class="text-muted fst-italic small">The reported item has already been deleted.</div>
                @endif
            </div>

            <!-- Action Controls -->
            @if($report->status === 'pending')
                <div class="d-flex align-items-center gap-2 pt-2 border-top flex-wrap">
                    <form action="{{ route('admin.reports.dismiss', $report) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Dismiss Report
                        </button>
                    </form>

                    @if($report->reportable)
                        <form action="{{ route('admin.reports.deleteContent', $report) }}" method="POST" onsubmit="return confirm('Delete this content?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash me-1"></i> Delete Content & Resolve
                            </button>
                        </form>

                        <form action="{{ route('admin.reports.deleteContent', $report) }}" method="POST" onsubmit="return confirm('Delete content AND suspend author?');">
                            @csrf
                            <input type="hidden" name="suspend_author" value="1">
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-slash-circle me-1"></i> Delete & Suspend Author
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="dg-card p-5 text-center">
            <i class="bi bi-shield-check text-success display-4"></i>
            <h5 class="mt-3 text-secondary">No reports in this category</h5>
            <p class="small text-muted mb-0">The community content is clean and well moderated.</p>
        </div>
    @endforelse
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $reports->links() }}
</div>
@endsection
