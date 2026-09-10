<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display listing of all discussion categories with real-time search.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $query = Category::withCount('questions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('slug', 'LIKE', "%{$search}%");
            });
        }

        $categories = $query->orderByDesc('questions_count')->paginate(12)->withQueryString();

        return view('categories.index', compact('categories', 'search'));
    }

    /**
     * Display questions under a specific category with search, filtering, and sorting.
     */
    public function show(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();

        $query = Question::with(['user', 'tags', 'acceptedAnswer'])
            ->where('category_id', $category->id);

        $search = trim((string) $request->query('q', ''));
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $sort = $request->query('sort', 'newest');
        switch ($sort) {
            case 'votes':
                $query->orderByDesc('vote_score')->latest();
                break;
            case 'unanswered':
                $query->where('answer_count', 0)->latest();
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $questions = $query->paginate(15)->withQueryString();

        return view('categories.show', compact('category', 'questions', 'sort', 'search'));
    }
}
