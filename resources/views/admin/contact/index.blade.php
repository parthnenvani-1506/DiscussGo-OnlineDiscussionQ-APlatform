@extends('layouts.admin')

@section('title', 'Contact Messages - DiscussHub Admin')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">Contact Messages & Feedback</h2>
        <p class="text-secondary small mb-0">{{ $messages->total() }} total messages from users</p>
    </div>
</div>

<div class="dg-card p-3 mb-4">
    <div class="d-flex gap-2">
        <a href="{{ route('admin.contact.index') }}" class="btn btn-sm rounded-pill px-3 {{ empty($status) ? 'btn-primary' : 'btn-outline-secondary' }}">
            All Messages
        </a>
        <a href="{{ route('admin.contact.index', ['status' => 'unread']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'unread' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Unread
        </a>
        <a href="{{ route('admin.contact.index', ['status' => 'read']) }}" class="btn btn-sm rounded-pill px-3 {{ $status === 'read' ? 'btn-primary' : 'btn-outline-secondary' }}">
            Read
        </a>
    </div>
</div>

<div class="dg-card overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Message Preview</th>
                    <th>Status</th>
                    <th>Received</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $msg)
                    <tr class="{{ !$msg->is_read ? 'fw-semibold bg-light' : '' }}">
                        <td>
                            <div class="text-dark">{{ $msg->name }}</div>
                            <div class="text-muted small">{{ $msg->email }}</div>
                        </td>
                        <td>{{ $msg->subject }}</td>
                        <td class="text-truncate" style="max-width: 250px;">{{ Str::limit($msg->message, 70) }}</td>
                        <td>
                            @if($msg->is_read)
                                <span class="badge bg-light text-secondary border">Read</span>
                            @else
                                <span class="badge bg-primary">New</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $msg->created_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('admin.contact.show', $msg) }}" class="btn btn-sm btn-outline-primary" title="Read message">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <form action="{{ route('admin.contact.destroy', $msg) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this message?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 d-flex justify-content-center">
    {{ $messages->links() }}
</div>
@endsection
