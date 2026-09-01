<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Display deep platform analytics and statistics.
     */
    public function index(): View
    {
        $totalQuestions = Question::count();
        $answeredQuestions = Question::where('is_answered', true)->count();
        $acceptanceRate = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100, 1) : 0;

        $totalAnswers = Answer::count();
        $avgAnswersPerQuestion = $totalQuestions > 0 ? round($totalAnswers / $totalQuestions, 1) : 0;

        $totalVotes = Vote::count();
        $totalUsers = User::count();

        // 30-day question and answer trends
        $monthlyDates = collect();
        $monthlyQuestions = [];
        $monthlyAnswers = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $monthlyDates->push($date->format('M d'));

            $monthlyQuestions[] = Question::whereDate('created_at', $dateStr)->count();
            $monthlyAnswers[] = Answer::whereDate('created_at', $dateStr)->count();
        }

        // Top 10 tags by usage
        $topTags = Tag::orderByDesc('usage_count')->take(10)->get();

        // Top 5 most active contributors
        $topContributors = User::where('is_suspended', false)
            ->withCount(['questions', 'answers'])
            ->orderByDesc('reputation')
            ->take(5)
            ->get();

        // Category breakdown
        $categories = Category::withCount('questions')->orderByDesc('questions_count')->get();

        return view('admin.analytics.index', compact(
            'totalQuestions',
            'answeredQuestions',
            'acceptanceRate',
            'totalAnswers',
            'avgAnswersPerQuestion',
            'totalVotes',
            'totalUsers',
            'monthlyDates',
            'monthlyQuestions',
            'monthlyAnswers',
            'topTags',
            'topContributors',
            'categories'
        ));
    }
}
