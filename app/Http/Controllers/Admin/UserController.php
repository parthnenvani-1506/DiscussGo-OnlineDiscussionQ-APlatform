<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display listing of all registered users.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $status = $request->query('status');

        $query = User::withCount(['questions', 'answers']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('city', 'LIKE', "%{$search}%");
            });
        }

        if ($status === 'suspended') {
            $query->where('is_suspended', true);
        } elseif ($status === 'active') {
            $query->where('is_suspended', false);
        }

        $users = $query->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'status'));
    }

    /**
     * Display detailed profile and activity of a user.
     */
    public function show(User $user): View
    {
        $user->load(['badges', 'reputationTransactions' => fn($q) => $q->latest()->take(20)]);

        $questions = $user->questions()->with(['category'])->latest()->paginate(10, ['*'], 'questions_page');
        $answers = $user->answers()->with(['question'])->latest()->paginate(10, ['*'], 'answers_page');

        return view('admin.users.show', compact('user', 'questions', 'answers'));
    }

    /**
     * Toggle suspension status of a user.
     */
    public function toggleSuspend(Request $request, User $user): RedirectResponse
    {
        $adminId = session('admin_id');

        if ($user->is_suspended) {
            $user->update([
                'is_suspended' => false,
                'suspended_reason' => null,
            ]);

            AuditLog::create([
                'admin_id' => $adminId,
                'action' => 'unsuspend_user',
                'details' => "Unsuspended user #{$user->id} ({$user->user_name})",
            ]);

            return back()->with('success', "User {$user->user_name} has been unsuspended.");
        } else {
            $reason = $request->input('reason', 'Account suspended due to policy violations.');
            $user->update([
                'is_suspended' => true,
                'suspended_reason' => $reason,
            ]);

            AuditLog::create([
                'admin_id' => $adminId,
                'action' => 'suspend_user',
                'details' => "Suspended user #{$user->id} ({$user->user_name}). Reason: {$reason}",
            ]);

            return back()->with('info', "User {$user->user_name} has been suspended.");
        }
    }

    /**
     * Permanently delete a user account and associated content.
     */
    public function destroy(User $user): RedirectResponse
    {
        $adminId = session('admin_id');
        $userName = $user->user_name;

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => 'delete_user',
            'details' => "Deleted user account #{$user->id} ({$userName})",
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User account {$userName} was deleted successfully.");
    }
}
