<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display moderation queue with reported content.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = Report::with(['reporter', 'reportable']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $reports = $query->latest()->paginate(20)->withQueryString();

        return view('admin.reports.index', compact('reports', 'status'));
    }

    /**
     * Dismiss a report without deleting content.
     */
    public function dismiss(Report $report): RedirectResponse
    {
        $adminId = session('admin_id');

        $report->update(['status' => 'dismissed']);

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => 'dismiss_report',
            'details' => "Dismissed moderation report #{$report->id} on {$report->reportable_type} #{$report->reportable_id}",
        ]);

        return back()->with('info', 'Report marked as dismissed.');
    }

    /**
     * Delete reported content and mark report as resolved.
     */
    public function deleteContent(Report $report, Request $request): RedirectResponse
    {
        $adminId = session('admin_id');
        $item = $report->reportable;

        if ($item) {
            $author = $item->user;

            // Delete item
            $item->delete();

            // Optionally suspend author
            if ($request->boolean('suspend_author') && $author) {
                $author->update([
                    'is_suspended' => true,
                    'suspended_reason' => 'Account suspended due to confirmed report: ' . $report->reason,
                ]);
            }
        }

        $report->update(['status' => 'resolved']);

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => 'resolve_report_delete',
            'details' => "Deleted reported {$report->reportable_type} #{$report->reportable_id} and resolved report #{$report->id}",
        ]);

        return back()->with('success', 'Reported content was deleted and report has been marked resolved.');
    }
}
