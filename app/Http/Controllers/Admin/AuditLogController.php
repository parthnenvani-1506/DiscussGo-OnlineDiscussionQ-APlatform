<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display administrative audit trail.
     */
    public function index(Request $request): View
    {
        $action = $request->query('action');
        $adminId = $request->query('admin_id');

        $query = AuditLog::with('admin');

        if ($action) {
            $query->where('action', $action);
        }

        if ($adminId) {
            $query->where('admin_id', $adminId);
        }

        $logs = $query->latest()->paginate(25)->withQueryString();
        $admins = Admin::all();
        $distinctActions = AuditLog::distinct()->pluck('action');

        return view('admin.audit-logs.index', compact('logs', 'admins', 'distinctActions', 'action', 'adminId'));
    }
}
