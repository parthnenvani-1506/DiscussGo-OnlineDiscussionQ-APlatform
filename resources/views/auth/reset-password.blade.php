@extends('layouts.auth')

@section('title', 'Set New Password - DiscussHub')

@section('content')

{{-- Header --}}
<div class="mb-4 text-center">
    <div class="auth-otp-icon mx-auto mb-3 auth-otp-icon--green">
        <i class="bi bi-key-fill"></i>
    </div>
    <h3 class="fw-bold text-dark mb-1">Set a new password</h3>
    <p class="text-secondary small mb-0">
        Your identity has been verified. Choose a strong password for your account.
    </p>
</div>

{{-- Form --}}
<form action="{{ route('password.reset.submit') }}" method="POST" novalidate>
    @csrf

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold">New Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted">
                <i class="bi bi-lock"></i>
            </span>
            <input
                type="password"
                class="form-control form-control-dg border-start-0 ps-0 @error('password') is-invalid @enderror"
                id="password"
                name="password"
                required
                autofocus
                placeholder="Min 6 characters"
                autocomplete="new-password"
            >
            <button type="button" class="btn btn-outline-secondary border-start-0 bg-transparent"
                id="toggle-password" tabindex="-1" title="Show/hide password">
                <i class="bi bi-eye" id="toggle-password-icon"></i>
            </button>
        </div>
        @error('password')
            <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror

        {{-- Strength meter --}}
        <div class="mt-2">
            <div class="progress" style="height:4px;border-radius:99px;">
                <div class="progress-bar" id="strength-bar" role="progressbar" style="width:0%;transition:width .3s,background .3s;"></div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <span class="text-muted" style="font-size:0.7rem;">Password strength</span>
                <span id="strength-label" style="font-size:0.7rem;" class="text-muted fw-semibold"></span>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label small fw-semibold">Confirm New Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0 text-muted">
                <i class="bi bi-lock-fill"></i>
            </span>
            <input
                type="password"
                class="form-control form-control-dg border-start-0 ps-0"
                id="password_confirmation"
                name="password_confirmation"
                required
                placeholder="Repeat new password"
                autocomplete="new-password"
            >
        </div>
        <div id="match-hint" class="small mt-1"></div>
    </div>

    {{-- Requirements checklist --}}
    <div class="bg-light rounded-3 p-3 mb-4 border" style="font-size:0.78rem;">
        <div class="fw-semibold text-dark mb-2">Password requirements</div>
        <div class="d-flex flex-column gap-1">
            <div id="req-length"  class="req-item text-muted"><i class="bi bi-circle me-2"></i>At least 6 characters</div>
            <div id="req-upper"   class="req-item text-muted"><i class="bi bi-circle me-2"></i>One uppercase letter (recommended)</div>
            <div id="req-number"  class="req-item text-muted"><i class="bi bi-circle me-2"></i>One number (recommended)</div>
            <div id="req-special" class="req-item text-muted"><i class="bi bi-circle me-2"></i>One special character (recommended)</div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-pill mb-3" id="reset-submit-btn">
        <i class="bi bi-check-circle me-1"></i> Reset Password
    </button>
</form>

<div class="text-center small text-secondary">
    <a href="{{ route('login') }}" class="text-secondary text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Back to Sign In
    </a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pwdInput   = document.getElementById('password');
    const confInput  = document.getElementById('password_confirmation');
    const bar        = document.getElementById('strength-bar');
    const label      = document.getElementById('strength-label');
    const matchHint  = document.getElementById('match-hint');
    const toggleBtn  = document.getElementById('toggle-password');
    const toggleIcon = document.getElementById('toggle-password-icon');

    const reqs = {
        length:  document.getElementById('req-length'),
        upper:   document.getElementById('req-upper'),
        number:  document.getElementById('req-number'),
        special: document.getElementById('req-special'),
    };

    // ── Show / hide password toggle ───────────────────────────────────
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isText = pwdInput.type === 'text';
            pwdInput.type = isText ? 'password' : 'text';
            toggleIcon.className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
        });
    }

    // ── Strength meter ────────────────────────────────────────────────
    function checkStrength(pwd) {
        let score = 0;
        const checks = {
            length:  pwd.length >= 6,
            upper:   /[A-Z]/.test(pwd),
            number:  /[0-9]/.test(pwd),
            special: /[^A-Za-z0-9]/.test(pwd),
        };

        // Update requirement icons
        Object.entries(checks).forEach(([key, pass]) => {
            const el = reqs[key];
            if (!el) return;
            el.className = 'req-item ' + (pass ? 'text-success' : 'text-muted');
            el.querySelector('i').className = pass
                ? 'bi bi-check-circle-fill me-2'
                : 'bi bi-circle me-2';
        });

        if (checks.length)  score += 25;
        if (checks.upper)   score += 25;
        if (checks.number)  score += 25;
        if (checks.special) score += 25;

        let color, text;
        if (score <= 25)      { color = '#ef4444'; text = 'Weak'; }
        else if (score <= 50) { color = '#f97316'; text = 'Fair'; }
        else if (score <= 75) { color = '#eab308'; text = 'Good'; }
        else                  { color = '#22c55e'; text = 'Strong'; }

        bar.style.width      = score + '%';
        bar.style.background = color;
        label.textContent    = pwd.length ? text : '';
        label.style.color    = color;
    }

    pwdInput.addEventListener('input', () => {
        checkStrength(pwdInput.value);
        checkMatch();
    });

    // ── Confirm match ─────────────────────────────────────────────────
    function checkMatch() {
        if (!confInput.value) { matchHint.textContent = ''; return; }
        if (pwdInput.value === confInput.value) {
            matchHint.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i><span class="text-success">Passwords match</span>';
        } else {
            matchHint.innerHTML = '<i class="bi bi-x-circle-fill text-danger me-1"></i><span class="text-danger">Passwords do not match</span>';
        }
    }

    confInput.addEventListener('input', checkMatch);
});
</script>
@endpush

@endsection
