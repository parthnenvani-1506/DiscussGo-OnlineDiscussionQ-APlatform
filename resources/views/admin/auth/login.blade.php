@extends('layouts.auth')

@section('title', 'Admin Sign In - DiscussHub Control Center')

@section('content')
<div class="mb-4 text-center">
    <div class="p-2 rounded-circle bg-danger bg-opacity-10 text-danger d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
        <i class="bi bi-shield-lock-fill fs-3"></i>
    </div>
    <h4 class="fw-bold text-dark">Admin Console</h4>
    <p class="text-secondary small">Restricted area for platform administrators</p>
</div>

<form action="{{ route('admin.login.post') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold">Admin Email or Username</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-shield-check"></i></span>
            <input type="text" class="form-control form-control-dg border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', 'admin@discusshub.ai') }}" required autofocus placeholder="admin or admin@discusshub.ai">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password" class="form-label small fw-semibold">Master Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-key-fill"></i></span>
            <input type="password" class="form-control form-control-dg border-start-0 ps-0 @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold rounded-pill">
        <i class="bi bi-unlock-fill me-1"></i> Authorize & Enter Console
    </button>
</form>
@endsection
