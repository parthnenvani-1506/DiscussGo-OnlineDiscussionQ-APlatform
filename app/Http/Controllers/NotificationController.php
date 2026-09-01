<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display all notifications for current user.
     */
    public function index(): View
    {
        $user = auth()->user();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read.
     */
    public function read(int $id): JsonResponse|RedirectResponse
    {
        $user = auth()->user();
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);

        $notification->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if (!empty($notification->data['link'])) {
            return redirect($notification->data['link']);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll(): JsonResponse|RedirectResponse
    {
        $user = auth()->user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get unread notification count via API.
     */
    public function unreadCount(): JsonResponse
    {
        $user = auth()->user();
        $count = $user ? Notification::where('user_id', $user->id)->where('is_read', false)->count() : 0;

        return response()->json(['count' => $count]);
    }
}
