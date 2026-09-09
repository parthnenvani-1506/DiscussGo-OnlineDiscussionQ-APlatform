<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Perform full-text and filtered search across all questions.
     */
    public function index(Request $request): View
    {
        $queryText = trim((string) $request->query('q', ''));
        $categoryId = $request->query('category');
        $tagSlug = $request->query('tag');
        $status = $request->query('status');
        $sort = $request->query('sort', 'relevance');

        $query = Question::with(['user', 'category', 'tags', 'acceptedAnswer']);

        if (!empty($queryText)) {
            // Check if FULLTEXT search is possible or fallback to LIKE
            try {
                $query->whereRaw("MATCH(title, description) AGAINST(? IN BOOLEAN MODE)", [$queryText . '*']);
            } catch (\Exception $e) {
                $query->where(function ($q) use ($queryText) {
                    $q->where('title', 'LIKE', "%{$queryText}%")
                      ->orWhere('description', 'LIKE', "%{$queryText}%");
                });
            }
        }

        // Filter by Category
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // Filter by Tag
        if (!empty($tagSlug)) {
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        // Filter by Status
        if ($status === 'answered') {
            $query->where('is_answered', true);
        } elseif ($status === 'unanswered') {
            $query->where('answer_count', 0);
        }

        // Sorting
        switch ($sort) {
            case 'newest':
                $query->latest();
                break;
            case 'votes':
                $query->orderByDesc('vote_score')->latest();
                break;
            case 'views':
                $query->orderByDesc('view_count')->latest();
                break;
            case 'relevance':
            default:
                if (!empty($queryText)) {
                    // Try to order by relevance if text provided
                    try {
                        $query->orderByRaw("MATCH(title, description) AGAINST(? IN BOOLEAN MODE) DESC", [$queryText . '*']);
                    } catch (\Exception $e) {
                        $query->latest();
                    }
                } else {
                    $query->latest();
                }
                break;
        }

        $questions = $query->paginate(15)->withQueryString();
        $categories = Category::withCount('questions')->get();
        $popularTags = Tag::orderByDesc('usage_count')->take(15)->get();

        return view('search.index', compact('questions', 'queryText', 'categories', 'popularTags', 'categoryId', 'tagSlug', 'status', 'sort'));
    }

    /**
     * Runtime Autocomplete / Live Search across Categories, Questions, Answers, Hashtags, and Users.
     */
    public function liveSearch(Request $request): JsonResponse
    {
        $rawQuery = trim((string) $request->query('q', ''));

        if (mb_strlen($rawQuery) < 1) {
            return response()->json([
                'query' => $rawQuery,
                'categories' => [],
                'questions' => [],
                'answers' => [],
                'hashtags' => [],
                'users' => [],
                'total_count' => 0,
            ]);
        }

        $cleanTagQuery = ltrim($rawQuery, '#');
        $cleanUserQuery = ltrim($rawQuery, '@');

        // 1. Categories
        $categories = Category::withCount('questions')
            ->where(function ($q) use ($rawQuery) {
                $q->where('name', 'LIKE', "%{$rawQuery}%")
                  ->orWhere('description', 'LIKE', "%{$rawQuery}%");
            })
            ->take(4)
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'description' => Str::limit(strip_tags($cat->description ?? ''), 65),
                    'questions_count' => $cat->questions_count ?? 0,
                    'url' => route('categories.show', $cat->slug),
                ];
            });

        // 2. Questions
        $questions = Question::with(['category', 'user'])
            ->where(function ($q) use ($rawQuery) {
                $q->where('title', 'LIKE', "%{$rawQuery}%")
                  ->orWhere('description', 'LIKE', "%{$rawQuery}%");
            })
            ->latest('id')
            ->take(4)
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'title' => $q->title,
                    'slug' => $q->slug ?? Str::slug($q->title),
                    'category' => $q->category ? $q->category->name : null,
                    'user_name' => $q->user ? $q->user->user_name : 'Anonymous',
                    'answer_count' => (int) $q->answer_count,
                    'vote_score' => (int) $q->vote_score,
                    'is_answered' => (bool) $q->is_answered,
                    'url' => route('questions.show', ['id' => $q->id, 'slug' => $q->slug ?? Str::slug($q->title)]),
                ];
            });

        // 3. Answers
        $answers = Answer::with(['question', 'user'])
            ->where('answer', 'LIKE', "%{$rawQuery}%")
            ->whereHas('question')
            ->latest('id')
            ->take(3)
            ->get()
            ->map(function ($ans) use ($rawQuery) {
                $plainText = strip_tags($ans->answer ?? '');
                // Generate a smart excerpt around the matched query
                $snippet = Str::limit($plainText, 100);
                if (!empty($rawQuery)) {
                    $pos = mb_stripos($plainText, $rawQuery);
                    if ($pos !== false && $pos > 30) {
                        $start = max(0, $pos - 25);
                        $snippet = '...' . mb_substr($plainText, $start, 90) . '...';
                    }
                }

                $question = $ans->question;
                $questionSlug = $question->slug ?? Str::slug($question->title ?? '');

                return [
                    'id' => $ans->id,
                    'snippet' => $snippet,
                    'question_id' => $question->id,
                    'question_title' => $question->title ?? 'Untitled Question',
                    'user_name' => $ans->user ? $ans->user->user_name : 'Community Member',
                    'vote_score' => (int) $ans->vote_score,
                    'is_accepted' => (bool) $ans->is_accepted,
                    'url' => route('questions.show', ['id' => $question->id, 'slug' => $questionSlug]) . '#answer-' . $ans->id,
                ];
            });

        // 4. Hashtags / Tags
        $hashtags = Tag::where(function ($q) use ($cleanTagQuery, $rawQuery) {
                $q->where('name', 'LIKE', "%{$cleanTagQuery}%")
                  ->orWhere('slug', 'LIKE', "%{$cleanTagQuery}%")
                  ->orWhere('name', 'LIKE', "%{$rawQuery}%");
            })
            ->orderByDesc('usage_count')
            ->take(5)
            ->get()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'usage_count' => (int) ($tag->usage_count ?? 0),
                    'url' => route('tags.show', $tag->slug),
                ];
            });

        // 5. Users
        $users = User::where(function ($q) use ($cleanUserQuery, $rawQuery) {
                $q->where('user_name', 'LIKE', "%{$cleanUserQuery}%")
                  ->orWhere('email', 'LIKE', "%{$cleanUserQuery}%")
                  ->orWhere('user_name', 'LIKE', "%{$rawQuery}%");
            })
            ->where('is_suspended', false)
            ->orderByDesc('reputation')
            ->take(4)
            ->get()
            ->map(function ($u) {
                $avatarUrl = null;
                if (!empty($u->profile_image) && $u->profile_image !== 'default_profile.png' && $u->profile_image !== 'user.jpg') {
                    $avatarUrl = asset('profiles/' . $u->profile_image);
                }

                return [
                    'id' => $u->id,
                    'user_name' => $u->user_name,
                    'initial' => strtoupper(substr($u->user_name ?? 'U', 0, 1)),
                    'avatar_url' => $avatarUrl,
                    'reputation' => (int) ($u->reputation ?? 0),
                    'level' => ucfirst($u->level ?? 'newcomer'),
                    'role' => $u->role ?? 'user',
                    'url' => route('users.show', $u->id),
                ];
            });

        $totalCount = $categories->count() + $questions->count() + $answers->count() + $hashtags->count() + $users->count();

        return response()->json([
            'query' => $rawQuery,
            'categories' => $categories,
            'questions' => $questions,
            'answers' => $answers,
            'hashtags' => $hashtags,
            'users' => $users,
            'total_count' => $totalCount,
            'full_search_url' => route('search', ['q' => $rawQuery]),
        ]);
    }
}
