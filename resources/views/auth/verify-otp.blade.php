@extends('layouts.auth')

@section('title', 'Enter Verification Code - DiscussHub')

@section('content')

{{-- Header --}}
<div class="mb-4 text-center">
    <div class="auth-otp-icon mx-auto mb-3 auth-otp-icon--amber">
        <i class="bi bi-envelope-check-fill"></i>
    </div>
    <h3 class="fw-bold text-dark mb-1">Check your email</h3>
    <p class="text-secondary small mb-0">
        We sent a <strong>6-digit code</strong> to
        @if(session('password_reset_email'))
            <strong class="text-dark">{{ \App\Http\Controllers\PasswordResetController::maskEmailStatic(session('password_reset_email')) }}</strong>
        @else
            your email address
        @endif
        . Enter it below to continue.
    </p>
</div>

{{-- OTP Form --}}
<form action="{{ route('password.verify-otp.submit') }}" method="POST" id="otp-form" novalidate>
    @csrf

    {{-- 6 individual digit inputs (UX) — joined into hidden field on submit --}}
    <div class="mb-4">
        <label class="form-label small fw-semibold d-block text-center mb-3">Verification Code</label>

        <div class="otp-input-group d-flex justify-content-center gap-2 mb-2" id="otp-boxes">
            @for($i = 1; $i <= 6; $i++)
                <input
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]"
                    maxlength="1"
                    class="otp-digit form-control form-control-dg text-center fw-bold fs-4 p-0"
                    style="width:52px;height:58px;"
                    autocomplete="off"
                    data-index="{{ $i }}"
                >
            @endfor
        </div>

        {{-- Hidden field that carries the joined OTP value --}}
        <input type="hidden" name="otp" id="otp-hidden" value="{{ old('otp') }}">

        @error('otp')
            <div class="text-danger small mt-2 text-center">
                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
            </div>
        @enderror
    </div>

    {{-- Expiry countdown --}}
    <div class="text-center mb-4">
        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">
            <i class="bi bi-clock me-1"></i>
            Code expires in <span id="otp-countdown" class="fw-bold">10:00</span>
        </span>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-pill mb-3" id="otp-submit-btn" disabled>
        <i class="bi bi-shield-check me-1"></i> Verify Code
    </button>
</form>

{{-- Resend --}}
<div class="text-center small text-secondary mb-3">
    Didn't receive the code?
    <form action="{{ route('password.verify-otp.resend') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-link btn-sm text-primary fw-semibold p-0 text-decoration-none">
            <i class="bi bi-arrow-clockwise me-1"></i>Resend Code
        </button>
    </form>
</div>

<div class="text-center small text-secondary">
    <a href="{{ route('password.forgot.form') }}" class="text-secondary text-decoration-none">
        <i class="bi bi-arrow-left me-1"></i>Use a different email
    </a>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const boxes      = document.querySelectorAll('.otp-digit');
    const hiddenOtp  = document.getElementById('otp-hidden');
    const submitBtn  = document.getElementById('otp-submit-btn');
    const form       = document.getElementById('otp-form');

    // ── 1. OTP digit-box UX ───────────────────────────────────────────
    boxes.forEach((box, idx) => {
        box.addEventListener('input', (e) => {
            // Allow only digits
            box.value = box.value.replace(/\D/g, '').slice(-1);

            syncHidden();

            // Auto-advance
            if (box.value && idx < boxes.length - 1) {
                boxes[idx + 1].focus();
            }
        });

        box.addEventListener('keydown', (e) => {
            // Backspace moves focus back
            if (e.key === 'Backspace' && !box.value && idx > 0) {
                boxes[idx - 1].focus();
            }
            // Left / Right arrow navigation
            if (e.key === 'ArrowLeft'  && idx > 0)              boxes[idx - 1].focus();
            if (e.key === 'ArrowRight' && idx < boxes.length - 1) boxes[idx + 1].focus();
        });

        // Handle paste (e.g. from clipboard or SMS)
        box.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(/\D/g, '')
                .slice(0, 6);
            [...pasted].forEach((char, i) => {
                if (boxes[i]) boxes[i].value = char;
            });
            const nextEmpty = [...boxes].findIndex(b => !b.value);
            if (nextEmpty !== -1) boxes[nextEmpty].focus();
            else boxes[boxes.length - 1].focus();
            syncHidden();
        });
    });

    function syncHidden() {
        const val = [...boxes].map(b => b.value).join('');
        hiddenOtp.value = val;
        submitBtn.disabled = val.length < 6;
    }

    // Auto-focus first box
    if (boxes[0]) boxes[0].focus();

    // Pre-fill from old() value if validation failed
    const existing = hiddenOtp.value.replace(/\D/g, '');
    if (existing.length === 6) {
        [...existing].forEach((c, i) => { if (boxes[i]) boxes[i].value = c; });
        syncHidden();
    }

    // ── 2. Countdown timer (10 min) ───────────────────────────────────
    const countdownEl = document.getElementById('otp-countdown');
    let seconds = 10 * 60; // 10 minutes

    const tick = () => {
        if (seconds <= 0) {
            countdownEl.textContent = 'Expired';
            countdownEl.closest('.badge').classList.remove('bg-warning-subtle', 'text-warning', 'border-warning-subtle');
            countdownEl.closest('.badge').classList.add('bg-danger-subtle', 'text-danger', 'border-danger-subtle');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Code Expired — Request a New One';
            return;
        }
        const m = String(Math.floor(seconds / 60)).padStart(2, '0');
        const s = String(seconds % 60).padStart(2, '0');
        countdownEl.textContent = `${m}:${s}`;
        seconds--;
        setTimeout(tick, 1000);
    };
    tick();
});
</script>
@endpush

@endsection
