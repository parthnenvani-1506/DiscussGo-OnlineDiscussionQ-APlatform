@extends('layouts.app')

@section('title', 'Your Notifications - DiscussHub')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="bi bi-bell-fill text-primary me-2"></i> Notifications</h2>
                <p class="text-secondary small mb-0">Stay updated on answers, upvotes, and achievements</p>
            </div>
            @if($notifications->where('is_read', false)->count() > 0)
                <form action="{{ route('notifications.readAll') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-check2-all me-1"></i> Mark All as Read
                    </button>
                </form>
            @endif
        </div>

        <div class="dg-card overflow-hidden">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data ?? [];
                    $type = $notification->type;
                    $icon = 'bi-bell';
                    $iconColor = 'text-primary';

                    if ($type === 'answer_posted') {
                        $icon = 'bi-chat-left-dots-fill';
                        $iconColor = 'text-primary';
                    } elseif ($type === 'answer_accepted') {
                        $icon = 'bi-check-circle-fill';
                        $iconColor = 'text-success';
                    } elseif ($type === 'upvote_received') {
                        $icon = 'bi-caret-up-square-fill';
                        $iconColor = 'text-info';
                    } elseif ($type === 'badge_earned') {
                        $icon = 'bi-award-fill';
                        $iconColor = 'text-warning';
                    }
                @endphp

                <div class="p-3 border-bottom d-flex align-items-start gap-3 {{ $notification->is_read ? 'bg-transparent' : 'bg-primary-subtle bg-opacity-25' }}" style="transition: var(--transition);">
                    <div class="p-2 rounded-circle bg-light flex-shrink-0">
                        <i class="bi {{ $icon }} {{ $iconColor }} fs-5"></i>
                    </div>

                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark small mb-1">
                            {{ $data['message'] ?? 'You have a new notification.' }}
                        </div>
                        <div class="d-flex align-items-center gap-3 small text-muted">
                            <span><i class="bi bi-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}</span>
                            @if(!empty($data['link']))
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link btn-sm text-primary p-0 text-decoration-none">
                                        View Discussion <i class="bi bi-arrow-right"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if(!$notification->is_read)
                        <span class="badge rounded-pill bg-primary" style="font-size: 0.6rem;">New</span>
                    @endif
                </div>
            @empty
                <div class="text-center py-5">
                    <i class="bi bi-bell-slash text-muted display-4"></i>
                    <h5 class="mt-3 text-secondary">No notifications right now</h5>
                    <p class="small text-muted mb-0">When someone answers your questions or upvotes your solutions, you will see it here.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
