@extends('layouts.auth')

@section('title', 'Create Account - DiscussHub')

@section('content')
<div class="mb-4 text-center">
    <h3 class="fw-bold text-dark">Join DiscussHub</h3>
    <p class="text-secondary small">Get +10 reputation points instantly and access smart Q&A</p>
</div>

<form action="{{ route('register.post') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="user_name" class="form-label small fw-semibold">Username</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control form-control-dg border-start-0 ps-0 @error('user_name') is-invalid @enderror" id="user_name" name="user_name" value="{{ old('user_name') }}" required autofocus placeholder="developer_name">
        </div>
        @error('user_name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control form-control-dg border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="name@example.com">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="city" class="form-label small fw-semibold">City (Optional)</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-geo-alt"></i></span>
            <input type="text" class="form-control form-control-dg border-start-0 ps-0 @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city') }}" placeholder="Ahmedabad, India">
        </div>
        @error('city')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="row g-2 mb-4">
        <div class="col-sm-6">
            <label for="password" class="form-label small fw-semibold">Password</label>
            <input type="password" class="form-control form-control-dg @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Min 6 chars">
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-sm-6">
            <label for="password_confirmation" class="form-label small fw-semibold">Confirm Password</label>
            <input type="password" class="form-control form-control-dg" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password">
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-pill mb-3">
        <i class="bi bi-person-plus me-1"></i> Register & Get 10 Pts
    </button>

    <div class="text-center small text-secondary">
        Already have an account? <a href="{{ route('login') }}" class="fw-semibold text-primary">Sign in</a>
    </div>
</form>
@endsection
