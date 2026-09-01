<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ModerationAction;
use App\Models\Question;
use App\Models\Report;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ActionController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Remove a question with mandatory reason.
     */
    public function removeQuestion(Request $request, Question $question): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);

        $owner = $question->user;
        $reason = $request->reason;

        $question->update([
            'is_flagged' => true,
            'is_removed' => false, // soft mark but actually delete
        ]);
        $question->delete();

        $this->logAction('remove_question', 'question', $question->id, $reason, $request->report_id ?? null, (bool)$request->ai_flag_source);

        // Notify content owner
        if ($owner) {
            $this->notificationService->contentRemoved($owner, 'question', $reason);
        }

        return back()->with('success', 'Question removed and owner notified.');
    }

    /**
     * Remove an answer with mandatory reason.
     */
    public function removeAnswer(Request $request, Answer $answer): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);

        $owner = $answer->user;
        $question = $answer->question;
        $reason = $request->reason;

        if ($question && $question->answer_count > 0) {
            $question->decrement('answer_count');
        }

        $answer->delete();

        $this->logAction('remove_answer', 'answer', $answer->id, $reason, $request->report_id ?? null, (bool)$request->ai_flag_source);

        if ($owner) {
            $this->notificationService->contentRemoved($owner, 'answer', $reason);
        }

        return back()->with('success', 'Answer removed and owner notified.');
    }

    /**
     * Warn a user.
     */
    public function warnUser(Request $request, User $user): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);

        $reason = $request->reason;

        $user->increment('warning_count');

        $this->logAction('warn_user', 'user', $user->id, $reason);

        $this->notificationService->userWarned($user, $reason);

        return back()->with('success', "Warning issued to {$user->user_name}.");
    }

    /**
     * Temporarily suspend a user.
     */
    public function suspendUser(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
            'days'   => 'required|in:1,3,7,30',
        ]);

        $reason = $request->reason;
        $days   = (int) $request->days;

        $user->update([
            'is_suspended'     => true,
            'suspended_reason' => $reason,
        ]);

        $this->logAction('suspend_user', 'user', $user->id, "Suspended for {$days} days. Reason: {$reason}");

        $this->notificationService->userSuspended($user, $reason);

        return back()->with('success', "{$user->user_name} suspended for {$days} days.");
    }

    /**
     * Dismiss an AI flag as false positive.
     */
    public function dismissFlag(Request $request, string $type, int $id): RedirectResponse
    {
        $model = $type === 'question'
            ? Question::findOrFail($id)
            : Answer::findOrFail($id);

        $model->update(['is_flagged' => false]);

        $this->logAction('dismiss_flag', $type, $id, 'Dismissed as false positive.');

        return back()->with('info', 'AI flag dismissed.');
    }

    /**
     * Dismiss a user report.
     */
    public function dismissReport(Report $report): RedirectResponse
    {
        $report->update(['status' => 'dismissed']);

        $this->logAction('dismiss_report', 'report', $report->id, 'Report dismissed as not actionable.');

        return back()->with('info', 'Report dismissed.');
    }

    /**
     * Escalate user to admin for permanent ban.
     */
    public function escalate(Request $request, User $user): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);

        $this->logAction('escalate', 'user', $user->id, "Escalated to admin for permanent ban. Reason: {$request->reason}");

        return back()->with('warning', "{$user->user_name} has been escalated to the admin for review.");
    }

    private function logAction(string $type, string $targetType, int $targetId, string $reason, ?int $reportId = null, bool $aiSource = false): void
    {
        ModerationAction::create([
            'moderator_id'   => auth()->id(),
            'action_type'    => $type,
            'target_type'    => $targetType,
            'target_id'      => $targetId,
            'reason'         => $reason,
            'report_id'      => $reportId,
            'ai_flag_source' => $aiSource,
        ]);
    }
}
