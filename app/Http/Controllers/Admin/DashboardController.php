<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Question;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the comprehensive admin dashboard with real KPI metrics and analytics.
     */
    public function index(): View
    {
        $kpis = [
            'total_users' => User::count(),
            'new_users_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'total_questions' => Question::count(),
            'new_questions_week' => Question::where('created_at', '>=', now()->subDays(7))->count(),
            'total_answers' => Answer::count(),
            'new_answers_week' => Answer::where('created_at', '>=', now()->subDays(7))->count(),
            'total_categories' => Category::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'unread_contacts' => ContactMessage::where('is_read', false)->count(),
        ];

        // Past 7 days activity chart data
        $dates = collect();
        $userCounts = [];
        $questionCounts = [];
        $answerCounts = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $dates->push($date->format('M d'));

            $userCounts[] = User::whereDate('created_at', $dateStr)->count();
            $questionCounts[] = Question::whereDate('created_at', $dateStr)->count();
            $answerCounts[] = Answer::whereDate('created_at', $dateStr)->count();
        }

        // Top categories by question distribution
        $topCategories = Category::withCount('questions')
            ->orderByDesc('questions_count')
            ->take(5)
            ->get();

        $categoryLabels = $topCategories->pluck('name')->toArray();
        $categoryData = $topCategories->pluck('questions_count')->toArray();

        // Recent questions
        $recentQuestions = Question::with(['user', 'category'])->latest()->take(5)->get();

        // Recent contact messages
        $recentMessages = ContactMessage::latest()->take(5)->get();

        // Pending reports
        $pendingReports = Report::with(['reporter'])->where('status', 'pending')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'kpis',
            'dates',
            'userCounts',
            'questionCounts',
            'answerCounts',
            'categoryLabels',
            'categoryData',
            'recentQuestions',
            'recentMessages',
            'pendingReports'
        ));
    }
}
