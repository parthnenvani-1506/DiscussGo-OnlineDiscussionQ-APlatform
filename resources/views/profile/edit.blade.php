@extends('layouts.app')

@section('title', 'Edit Profile & Settings - DiscussHub')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Account Settings</h2>
            <p class="text-secondary small">Manage your public developer profile and personal details</p>
        </div>

        <div class="dg-card p-4">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Current Avatar Preview & Upload -->
                <div class="d-flex align-items-center gap-4 mb-4 pb-3 border-bottom">
                    @if($user->profile_image)
                        <img src="{{ asset('profiles/' . $user->profile_image) }}" class="rounded-circle object-fit-cover" width="80" height="80" alt="avatar">
                    @else
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 80px; height: 80px;">
                            {{ strtoupper(substr($user->user_name, 0, 1)) }}
                        </div>
                    @endif

                    <div class="flex-grow-1">
                        <label for="profile_image" class="form-label small fw-semibold">Profile Photo</label>
                        <input type="file" name="profile_image" id="profile_image" class="form-control form-control-dg @error('profile_image') is-invalid @enderror" accept="image/*">
                        <div class="form-text small">JPEG, PNG, WEBP (Max 2MB)</div>
                        @error('profile_image')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="user_name" class="form-label small fw-semibold">Username</label>
                    <input type="text" name="user_name" id="user_name" class="form-control form-control-dg @error('user_name') is-invalid @enderror" value="{{ old('user_name', $user->user_name) }}" required>
                    @error('user_name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Email (Read Only)</label>
                    <input type="email" id="email" class="form-control form-control-dg bg-light" value="{{ $user->email }}" disabled>
                </div>

                <div class="mb-3">
                    <label for="city" class="form-label small fw-semibold">Location / City</label>
                    <input type="text" name="city" id="city" class="form-control form-control-dg @error('city') is-invalid @enderror" value="{{ old('city', $user->city) }}" placeholder="e.g. Bangalore, India">
                    @error('city')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="bio" class="form-label small fw-semibold">Bio / About You</label>
                    <textarea name="bio" id="bio" rows="4" class="form-control form-control-dg @error('bio') is-invalid @enderror" placeholder="Tell other developers about your specialties, tools, and background...">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn-primary-dg px-4 py-2">
                        <i class="bi bi-check2-circle me-1"></i> Save Changes
                    </button>
                    <a href="{{ route('profile.show') }}" class="btn-secondary-dg px-3 py-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
