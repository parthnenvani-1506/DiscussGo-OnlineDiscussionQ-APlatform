<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
}
