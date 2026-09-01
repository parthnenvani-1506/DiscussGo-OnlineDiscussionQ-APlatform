<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected RecommendationService $recommendationService
    ) {}

    /**
     * Show the application home / discussion feed.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $feedFilter = $request->query('feed', $user ? 'recommended' : 'latest');

        if ($feedFilter === 'recommended' && $user) {
            $questions = $this->recommendationService->getPersonalizedFeed($user, 15);
        } elseif ($feedFilter === 'following' && $user) {
            $questions = $this->recommendationService->getFollowingFeed($user, 15);
        } elseif ($feedFilter === 'trending') {
            $questions = Question::with(['user', 'category', 'tags', 'acceptedAnswer'])
                ->orderByRaw('(vote_score * 2 + answer_count * 3 + view_count * 0.1) DESC')
                ->latest()
                ->paginate(15)
                ->appends(['feed' => 'trending']);
        } elseif ($feedFilter === 'unanswered') {
            $questions = Question::with(['user', 'category', 'tags'])
                ->where('answer_count', 0)
                ->latest()
                ->paginate(15)
                ->appends(['feed' => 'unanswered']);
        } else {
            $questions = Question::with(['user', 'category', 'tags', 'acceptedAnswer'])
                ->latest()
                ->paginate(15)
                ->appends(['feed' => 'latest']);
        }

        $topCategories = Category::withCount('questions')->orderByDesc('questions_count')->take(8)->get();
        $popularTags = Tag::orderByDesc('usage_count')->take(15)->get();
        $topUsers = User::where('is_suspended', false)->orderByDesc('reputation')->take(5)->get();

        return view('home', compact('questions', 'feedFilter', 'topCategories', 'popularTags', 'topUsers'));
    }
}
