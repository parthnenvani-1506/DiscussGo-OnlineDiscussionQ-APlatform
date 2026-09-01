<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Display list of contact messages.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = ContactMessage::query();

        if ($status === 'unread') {
            $query->where('is_read', false);
        } elseif ($status === 'read') {
            $query->where('is_read', true);
        }

        $messages = $query->latest()->paginate(20)->withQueryString();

        return view('admin.contact.index', compact('messages', 'status'));
    }

    /**
     * View message details and mark as read.
     */
    public function show(ContactMessage $contact): View
    {
        if (!$contact->is_read) {
            $contact->update(['is_read' => true]);
        }

        return view('admin.contact.show', compact('contact'));
    }

    /**
     * Toggle read/unread state of message.
     */
    public function toggleRead(ContactMessage $contact): RedirectResponse
    {
        $contact->update(['is_read' => !$contact->is_read]);
        return back()->with('success', 'Message status updated.');
    }

    /**
     * Delete contact message.
     */
    public function destroy(ContactMessage $contact): RedirectResponse
    {
        $contact->delete();
        return redirect()->route('admin.contact.index')->with('success', 'Message deleted.');
    }
}
