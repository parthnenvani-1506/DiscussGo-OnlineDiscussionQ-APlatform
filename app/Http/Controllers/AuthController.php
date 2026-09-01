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
        $credentials = $request->only('email', 'password');

        // Check if user exists with password_reset_required or legacy MD5 password
        $user = User::where('email', $request->email)->first();
        if ($user && $user->password_reset_required) {
            // Legacy user attempting login with MD5
            if (md5($request->password) === $user->password || Hash::check($request->password, $user->password)) {
                // Upgrade password to Bcrypt and clear reset flag
                $user->password = Hash::make($request->password);
                $user->password_reset_required = false;
                $user->save();

                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();

                // Redirect moderators to their panel
                if ($user->role === 'moderator') {
                    return redirect()->route('moderator.dashboard')
                        ->with('success', 'Welcome back! Your account security has been updated.');
                }

                return redirect()->intended(route('home'))->with('success', 'Welcome back! Your account security has been updated.');
            }
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if ($user->is_suspended) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'email' => 'Your account has been suspended by an administrator. Reason: ' . ($user->suspended_reason ?? 'Violation of community guidelines.'),
                ]);
            }

            $request->session()->regenerate();

            // Redirect moderators to their panel
            if ($user->role === 'moderator') {
                return redirect()->route('moderator.dashboard')
                    ->with('success', 'Welcome back, ' . $user->user_name . '! You are logged in to the Moderator Panel.');
            }

            return redirect()->intended(route('home'))
                ->with('success', 'Logged in successfully! Welcome back, ' . $user->user_name);
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
