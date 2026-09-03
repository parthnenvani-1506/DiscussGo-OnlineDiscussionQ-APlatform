<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $remember = $request->boolean('remember');

        // ── Legacy v1 migration path (MD5 passwords or password_reset_required) ──
        $user = User::where('email', $request->email)->first();
        if ($user && $user->password_reset_required) {
            $passwordValid = md5($request->password) === $user->password
                || Hash::check($request->password, $user->password);

            if ($passwordValid) {
                // Upgrade to bcrypt in-place, clear the reset flag
                $user->forceFill([
                    'password'               => Hash::make($request->password),
                    'password_reset_required' => false,
                ])->save();

                // Log the user in with remember-me support, then regenerate session
                Auth::login($user, $remember);
                $request->session()->regenerate();

                if ($user->role === 'moderator') {
                    return redirect()->route('moderator.dashboard')
                        ->with('success', 'Welcome back! Your account security has been updated.');
                }

                return redirect()->intended(route('home'))
                    ->with('success', 'Welcome back! Your account security has been updated.');
            }
        }

        // ── Standard credential check ─────────────────────────────────────────
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
            $user = Auth::user();

            if ($user->is_suspended) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Reason: '
                        . ($user->suspended_reason ?? 'Violation of community guidelines.'),
                ]);
            }

            $request->session()->regenerate();

            if ($user->role === 'moderator') {
                return redirect()->route('moderator.dashboard')
                    ->with('success', 'Welcome back, ' . $user->user_name . '! You are logged in to the Moderator Panel.');
            }

            return redirect()->intended(route('home'))
                ->with('success', 'Logged in successfully! Welcome back, ' . $user->user_name . '.');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'city' => $request->city,
            'reputation' => 10, // starting reputation bonus
            'level' => 'newcomer',
            'password_reset_required' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Account registered successfully! You have received 10 reputation points to get started.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been logged out.');
    }
}
