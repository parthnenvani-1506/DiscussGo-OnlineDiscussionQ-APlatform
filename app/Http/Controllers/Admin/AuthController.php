<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show admin login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (session()->has('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Authenticate admin user.
     */
    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->email);

        $admin = Admin::where('email', $loginInput)
            ->orWhere('username', $loginInput)
            ->orWhere('email', str_replace('@discusshub.com', '@discusshub.ai', $loginInput))
            ->orWhere('email', str_replace('@discusshub.ai', '@discusshub.com', $loginInput))
            ->first();

        if ($admin && Hash::check($request->password, $admin->password)) {

            session([
                'admin_id' => $admin->id,
                'admin_name' => $admin->username ?? 'Administrator',
                'admin_email' => $admin->email,
            ]);

            AuditLog::create([
                'admin_id' => $admin->id,
                'action' => 'login',
                'details' => 'Admin logged into administration panel from IP ' . $request->ip(),
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Welcome to DiscussHub Admin Control Center.');
        }

        return back()->withErrors([
            'email' => 'Invalid administrative credentials.',
        ])->onlyInput('email');
    }

    /**
     * Log admin out.
     */
    public function logout(Request $request): RedirectResponse
    {
        $adminId = session('admin_id');
        if ($adminId) {
            AuditLog::create([
                'admin_id' => $adminId,
                'action' => 'logout',
                'details' => 'Admin logged out.',
            ]);
        }

        session()->forget(['admin_id', 'admin_name', 'admin_email']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('info', 'You have been logged out of the Admin panel.');
    }
}
