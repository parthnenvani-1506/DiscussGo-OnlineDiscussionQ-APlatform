<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiRequest;
use App\Models\Answer;
use App\Models\Question;
use App\Services\AIService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AICenterController extends Controller
{
    public function __construct(
        protected AIService $aiService
    ) {}

    /**
     * Display the AI Control Center, metrics, and moderation queue.
     */
    public function index(): View
    {
        $ollamaAvailable = $this->aiService->isAvailable();

        $stats = [
            'total_today' => AiRequest::whereDate('created_at', today())->count(),
            'total_week' => AiRequest::where('created_at', '>=', now()->subDays(7))->count(),
            'total_all' => AiRequest::count(),
            'success_rate' => AiRequest::count() > 0 ? round((AiRequest::where('success', true)->count() / AiRequest::count()) * 100, 1) : 100,
            'avg_response_time' => round((float)AiRequest::avg('response_time'), 3),
        ];

        // Breakdown by feature
        $featureStats = AiRequest::select('feature', DB::raw('count(*) as total'), DB::raw('avg(response_time) as avg_time'))
            ->groupBy('feature')
            ->get();

        $featureLabels = $featureStats->pluck('feature')->map(fn($f) => ucfirst(str_replace('_', ' ', $f)))->toArray();
        $featureCounts = $featureStats->pluck('total')->toArray();

        // AI flagged content
        $flaggedQuestions = Question::with('user')->where('is_flagged', true)->latest()->take(10)->get();
        $flaggedAnswers = Answer::with(['user', 'question'])->where('is_flagged', true)->latest()->take(10)->get();

        // Recent 30 AI requests
        $recentRequests = AiRequest::latest()->take(30)->get();

        return view('admin.ai-center.index', compact(
            'ollamaAvailable',
            'stats',
            'featureLabels',
            'featureCounts',
            'featureStats',
            'flaggedQuestions',
            'flaggedAnswers',
            'recentRequests'
        ));
    }

    /**
     * Clear AI moderation flag on a question.
     */
    public function clearQuestionFlag(Question $question): RedirectResponse
    {
        $question->update(['is_flagged' => false]);
        return back()->with('success', 'Question flagged status cleared.');
    }

    /**
     * Clear AI moderation flag on an answer.
     */
    public function clearAnswerFlag(Answer $answer): RedirectResponse
    {
        $answer->update(['is_flagged' => false]);
        return back()->with('success', 'Answer flagged status cleared.');
    }
}
