@extends('layouts.auth')

@section('title', 'Sign In - DiscussHub')

@section('content')
<div class="mb-4 text-center">
    <h3 class="fw-bold text-dark">Welcome back</h3>
    <p class="text-secondary small">Enter your credentials to access your developer account</p>
</div>

<form action="{{ route('login.post') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Email address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control form-control-dg border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@example.com">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <label for="password" class="form-label small fw-semibold mb-0">Password</label>
            <a href="{{ route('password.forgot.form') }}" class="small text-primary text-decoration-none">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-key"></i></span>
            <input type="password" class="form-control form-control-dg border-start-0 ps-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label small text-secondary" for="remember">
            Keep me signed in on this device
        </label>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-pill mb-3">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
    </button>

    <div class="text-center small text-secondary">
        Don't have an account yet? <a href="{{ route('register') }}" class="fw-semibold text-primary">Create an account</a>
    </div>
</form>
@endsection
