@extends('layouts.app')
@section('title', $user->user_name . ' — Following - DiscussHub')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill mb-3">
                <i class="bi bi-arrow-left me-1"></i> Back to {{ $user->user_name }}'s profile
            </a>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-person-check-fill text-primary me-2"></i> {{ $user->user_name }} is Following
            </h2>
            <p class="text-secondary small mb-0">{{ $following->total() }} people {{ $user->user_name }} follows</p>
        </div>

        <div class="dg-card overflow-hidden">
            @forelse($following as $followed)
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        @if($followed->profile_image && $followed->profile_image !== 'default_profile.png')
                            <img src="{{ asset('profiles/' . $followed->profile_image) }}" class="rounded-circle object-fit-cover" width="44" height="44" alt="avatar">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width:44px;height:44px;">
                                {{ strtoupper(substr($followed->user_name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <a href="{{ route('users.show', $followed->id) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $followed->user_name }}
                            </a>
                            <div class="small text-muted d-flex align-items-center gap-2">
                                <span class="badge bg-light text-secondary border">{{ ucfirst($followed->level ?? 'newcomer') }}</span>
                                <span class="reputation-badge py-0 px-2"><i class="bi bi-stars"></i> {{ $followed->reputation }}</span>
                                @if($followed->city) <span><i class="bi bi-geo-alt me-1"></i>{{ $followed->city }}</span> @endif
                            </div>
                        </div>
                    </div>
                    @auth
                        @if(auth()->id() !== $followed->id)
                            <button class="btn btn-sm {{ auth()->user()->isFollowing($followed) ? 'btn-outline-secondary' : 'btn-primary' }} rounded-pill px-3 btn-follow-toggle"
                                data-user-id="{{ $followed->id }}">
                                {{ auth()->user()->isFollowing($followed) ? 'Following' : '+ Follow' }}
                            </button>
                        @endif
                    @endauth
                </div>
            @empty
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-plus fs-2 d-block mb-2"></i>
                    <p class="mb-0">Not following anyone yet.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4 d-flex justify-content-center">{{ $following->links() }}</div>
    </div>
</div>
@endsection
