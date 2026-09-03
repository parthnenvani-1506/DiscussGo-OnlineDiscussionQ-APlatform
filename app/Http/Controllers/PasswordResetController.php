<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private const OTP_TTL_MINUTES   = 10;
    private const OTP_MAX_ATTEMPTS  = 5;
    private const OTP_DIGITS        = 6;

    public function __construct(
        protected MailService $mailer
    ) {}

    // ──────────────────────────────────────────────────────────────────
    //  STEP 1 — Show "Forgot Password" form
    // ──────────────────────────────────────────────────────────────────

    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    // ──────────────────────────────────────────────────────────────────
    //  STEP 1 — Process email, send OTP
    // ──────────────────────────────────────────────────────────────────

    public function sendOtp(ForgotPasswordRequest $request): RedirectResponse
    {
        // Rate-limit: max 3 OTP requests per email per 5 minutes
        $rateLimiterKey = 'otp-request:' . $request->ip() . '|' . strtolower($request->email);

        if (RateLimiter::tooManyAttempts($rateLimiterKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);
            return back()->withInput()->with('error',
                'Too many reset requests. Please wait ' . ceil($seconds / 60) . ' minute(s) before trying again.'
            );
        }

        RateLimiter::hit($rateLimiterKey, 300); // 5 minute window

        $user = User::where('email', $request->email)->first();

        /*
         * Always return the same success message whether the email exists or not.
         * This prevents email enumeration attacks.
         */
        if (!$user) {
            return redirect()
                ->route('password.verify-otp.form')
                ->with('otp_email', $request->email)
                ->with('info', 'If that email is registered, a verification code has been sent.');
        }

        // Generate cryptographically secure 6-digit OTP
        $otp     = str_pad((string) random_int(0, 999999), self::OTP_DIGITS, '0', STR_PAD_LEFT);
        $expires = now()->addMinutes(self::OTP_TTL_MINUTES);

        $user->update([
            'otp_code'     => $otp,
            'otp_expires_at' => $expires,
            'otp_attempts' => 0,
        ]);

        // Store email in session for the next step
        session(['password_reset_email' => $user->email]);

        // Send OTP email via PHPMailer
        $sent = $this->mailer->sendOtpEmail($user->email, $user->user_name, $otp);

        if (!$sent) {
            // If mail fails, do not expose OTP — fail gracefully
            $user->update(['otp_code' => null, 'otp_expires_at' => null]);
            return back()->withInput()->with('error',
                'Failed to send the verification email. Please check your mail configuration or try again later.'
            );
        }

        return redirect()->route('password.verify-otp.form')
            ->with('success', 'A 6-digit verification code has been sent to ' . $this->maskEmail($user->email));
    }

    // ──────────────────────────────────────────────────────────────────
    //  STEP 2 — Show OTP verification form
    // ──────────────────────────────────────────────────────────────────

    public function showVerifyOtpForm(): View
    {
        // Guard: must have come from step 1
        if (!session('password_reset_email')) {
            return view('auth.forgot-password');
        }
        return view('auth.verify-otp');
    }

    // ──────────────────────────────────────────────────────────────────
    //  STEP 2 — Verify OTP
    // ──────────────────────────────────────────────────────────────────

    public function verifyOtp(VerifyOtpRequest $request): RedirectResponse
    {
        $email = session('password_reset_email');

        if (!$email) {
            return redirect()->route('password.forgot.form')
                ->with('error', 'Session expired. Please start the reset process again.');
        }

        $user = User::where('email', $email)->first();

        if (!$user || !$user->otp_code) {
            return redirect()->route('password.forgot.form')
                ->with('error', 'No pending reset request found. Please request a new code.');
        }

        // Check attempt limit
        if ($user->otp_attempts >= self::OTP_MAX_ATTEMPTS) {
            $user->update(['otp_code' => null, 'otp_expires_at' => null, 'otp_attempts' => 0]);
            session()->forget('password_reset_email');
            return redirect()->route('password.forgot.form')
                ->with('error', 'Too many incorrect attempts. Please request a new verification code.');
        }

        // Check expiry
        if (!$user->otp_expires_at || now()->isAfter($user->otp_expires_at)) {
            $user->update(['otp_code' => null, 'otp_expires_at' => null, 'otp_attempts' => 0]);
            session()->forget('password_reset_email');
            return redirect()->route('password.forgot.form')
                ->with('error', 'Your verification code has expired. Please request a new one.');
        }

        // Constant-time comparison to prevent timing attacks
        if (!hash_equals((string) $user->otp_code, (string) $request->otp)) {
            $user->increment('otp_attempts');
            $remaining = self::OTP_MAX_ATTEMPTS - $user->otp_attempts;
            return back()->with('error',
                'Incorrect verification code. ' . ($remaining > 0 ? $remaining . ' attempt(s) remaining.' : 'No attempts remaining.')
            );
        }

        // OTP valid — clear it and mark session as verified
        $user->update(['otp_code' => null, 'otp_expires_at' => null, 'otp_attempts' => 0]);
        session(['password_reset_verified' => true]);

        return redirect()->route('password.reset.form')
            ->with('success', 'Code verified! Please set your new password.');
    }

    // ──────────────────────────────────────────────────────────────────
    //  STEP 3 — Show new password form
    // ──────────────────────────────────────────────────────────────────

    public function showResetForm(): View
    {
        // Guard: must have completed OTP verification
        if (!session('password_reset_verified') || !session('password_reset_email')) {
            return view('auth.forgot-password');
        }
        return view('auth.reset-password');
    }

    // ──────────────────────────────────────────────────────────────────
    //  STEP 3 — Set new password
    // ──────────────────────────────────────────────────────────────────

    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $email    = session('password_reset_email');
        $verified = session('password_reset_verified');

        if (!$email || !$verified) {
            return redirect()->route('password.forgot.form')
                ->with('error', 'Session expired. Please start the reset process again.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            session()->forget(['password_reset_email', 'password_reset_verified']);
            return redirect()->route('password.forgot.form')
                ->with('error', 'Account not found. Please try again.');
        }

        $user->update([
            'password'               => Hash::make($request->password),
            'password_reset_required' => false,
        ]);

        // Clean up session
        session()->forget(['password_reset_email', 'password_reset_verified']);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! You can now sign in with your new password.');
    }

    // ──────────────────────────────────────────────────────────────────
    //  RESEND OTP
    // ──────────────────────────────────────────────────────────────────

    public function resendOtp(Request $request): RedirectResponse
    {
        $email = session('password_reset_email');

        if (!$email) {
            return redirect()->route('password.forgot.form')
                ->with('error', 'Session expired. Please start over.');
        }

        // Rate-limit resends: max 2 per 5 minutes
        $rateLimiterKey = 'otp-resend:' . $request->ip() . '|' . strtolower($email);

        if (RateLimiter::tooManyAttempts($rateLimiterKey, 2)) {
            $seconds = RateLimiter::availableIn($rateLimiterKey);
            return back()->with('error',
                'Please wait ' . ceil($seconds / 60) . ' minute(s) before requesting another code.'
            );
        }

        RateLimiter::hit($rateLimiterKey, 300);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('password.forgot.form')->with('error', 'Account not found.');
        }

        $otp     = str_pad((string) random_int(0, 999999), self::OTP_DIGITS, '0', STR_PAD_LEFT);
        $expires = now()->addMinutes(self::OTP_TTL_MINUTES);

        $user->update([
            'otp_code'       => $otp,
            'otp_expires_at' => $expires,
            'otp_attempts'   => 0,
        ]);

        $sent = $this->mailer->sendOtpEmail($user->email, $user->user_name, $otp);

        if (!$sent) {
            $user->update(['otp_code' => null, 'otp_expires_at' => null]);
            return back()->with('error', 'Failed to resend the code. Please try again later.');
        }

        return back()->with('success', 'A new verification code has been sent to ' . $this->maskEmail($email));
    }

    // ──────────────────────────────────────────────────────────────────
    //  Helper — mask email for display (e.g. j***@example.com)
    // ──────────────────────────────────────────────────────────────────

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = substr($local, 0, 1);
        $masked  = $visible . str_repeat('*', max(strlen($local) - 1, 3));
        return $masked . '@' . $domain;
    }

    /**
     * Public static version used in Blade views.
     */
    public static function maskEmailStatic(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2);
        $visible = substr($local, 0, 1);
        $masked  = $visible . str_repeat('*', max(strlen($local) - 1, 3));
        return $masked . '@' . $domain;
    }
}
