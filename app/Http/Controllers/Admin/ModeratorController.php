<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ModerationAction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModeratorController extends Controller
{
    /**
     * List all current moderators.
     */
    public function index(): View
    {
        $moderators = User::where('role', 'moderator')
            ->withCount(['questions', 'answers'])
            ->get();

        $recentActions = ModerationAction::with('moderator')
            ->latest()
            ->take(20)
            ->get();

        return view('admin.moderators.index', compact('moderators', 'recentActions'));
    }

    /**
     * Appoint a user as moderator.
     */
    public function appoint(User $user): RedirectResponse
    {
        if ($user->role === 'moderator') {
            return back()->with('info', "{$user->user_name} is already a moderator.");
        }

        $user->update(['role' => 'moderator']);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'appoint_moderator',
            'details'  => "Appointed {$user->user_name} (#{$user->id}) as moderator.",
        ]);

        return back()->with('success', "{$user->user_name} is now a moderator.");
    }

    /**
     * Remove moderator role from a user.
     */
    public function remove(User $user): RedirectResponse
    {
        $user->update(['role' => 'user']);

        AuditLog::create([
            'admin_id' => session('admin_id'),
            'action'   => 'remove_moderator',
            'details'  => "Removed moderator role from {$user->user_name} (#{$user->id}).",
        ]);

        return back()->with('info', "{$user->user_name}'s moderator role has been removed.");
    }
}
