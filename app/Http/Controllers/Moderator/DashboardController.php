<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\ModerationAction;
use App\Models\Question;
use App\Models\Report;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $moderator = auth()->user();

        $stats = [
            'ai_flags_pending'     => Question::where('is_flagged', true)->count()
                                    + Answer::where('is_flagged', true)->count(),
            'reports_pending'      => Report::where('status', 'pending')->count(),
            'actions_today'        => ModerationAction::where('moderator_id', $moderator->id)
                                        ->whereDate('created_at', today())->count(),
            'total_actions'        => ModerationAction::where('moderator_id', $moderator->id)->count(),
        ];

        $recentActions = ModerationAction::where('moderator_id', $moderator->id)
            ->latest()->take(5)->get();

        return view('moderator.dashboard', compact('stats', 'recentActions'));
    }
}
