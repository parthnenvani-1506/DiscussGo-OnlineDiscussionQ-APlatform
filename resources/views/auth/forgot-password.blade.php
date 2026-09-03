@extends('layouts.auth')

@section('title', 'Reset Password - DiscussHub')

@section('content')

{{-- Header --}}
<div class="mb-4 text-center">
    <div class="auth-otp-icon mx-auto mb-3">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <h3 class="fw-bold text-dark mb-1">Forgot your password?</h3>
    <p class="text-secondary small mb-0">
        Enter your registered email address and we'll send a
        <strong>6-digit verification code</strong> to reset your password.
    </p>
</div>

{{-- Form --}}
<form action="{{ route('password.forgot.send') }}" method="POST" novalidate>
    @csrf

    <div class="mb-4">
        <label for="email" class="form-label small fw-semibold">Email address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted">
                <i class="bi bi-envelope"></i>
            </span>
            <input
                type="email"
                class="form-control form-control-dg border-start-0 ps-0 @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                placeholder="name@example.com"
                autocomplete="email"
            >
        </div>
        @error('email')
            <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-pill mb-3">
        <i class="bi bi-send me-1"></i> Send Verification Code
    </button>
</form>

{{-- Divider --}}
<div class="auth-divider my-3">
    <span>or</span>
</div>

<div class="text-center small text-secondary">
    Remember your password?
    <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Back to Sign In
    </a>
</div>

@endsection
