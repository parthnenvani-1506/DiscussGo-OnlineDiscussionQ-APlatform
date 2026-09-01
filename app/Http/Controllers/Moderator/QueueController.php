<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ModerationAction;
use App\Models\Question;
use App\Models\Report;
use Illuminate\View\View;

class QueueController extends Controller
{
    /**
     * AI-flagged content queue.
     */
    public function aiQueue(): View
    {
        $flaggedQuestions = Question::with(['user', 'category'])
            ->where('is_flagged', true)
            ->latest()
            ->paginate(15, ['*'], 'q_page');

        $flaggedAnswers = Answer::with(['user', 'question'])
            ->where('is_flagged', true)
            ->latest()
            ->paginate(15, ['*'], 'a_page');

        return view('moderator.ai-queue', compact('flaggedQuestions', 'flaggedAnswers'));
    }

    /**
     * User report queue.
     */
    public function reportQueue(): View
    {
        $reports = Report::with(['reporter', 'reportable'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return view('moderator.report-queue', compact('reports'));
    }

    /**
     * Own moderation action history.
     */
    public function history(): View
    {
        $actions = ModerationAction::where('moderator_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('moderator.history', compact('actions'));
    }
}
