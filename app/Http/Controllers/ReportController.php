<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Submit a moderation report for a question or answer.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'reportable_type' => 'required|in:question,answer',
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|in:spam,offensive,duplicate,misleading,other',
            'details' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $type = $request->reportable_type;
        $id = (int) $request->reportable_id;

        $target = ($type === 'question')
            ? Question::findOrFail($id)
            : Answer::findOrFail($id);

        if ($target->user_id === $user->id) {
            return response()->json(['error' => 'You cannot report your own content.'], 422);
        }

        $alreadyReported = Report::where('reporter_id', $user->id)
            ->where('reportable_type', $type)
            ->where('reportable_id', $id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyReported) {
            return response()->json(['error' => 'You have already reported this item. Our moderation team is reviewing it.'], 422);
        }

        Report::create([
            'reporter_id' => $user->id,
            'reportable_type' => $type,
            'reportable_id' => $id,
            'reason' => $request->reason,
            'details' => $request->details,
            'status' => 'pending',
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you. The report has been submitted to the moderation team for review.',
            ]);
        }

        return back()->with('success', 'Report submitted successfully. Thank you for helping keep the community safe.');
    }
}
