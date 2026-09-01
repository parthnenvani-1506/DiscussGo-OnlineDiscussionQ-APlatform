<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display listing of all discussion categories.
     */
    public function index(): View
    {
        $categories = Category::withCount('questions')
            ->orderByDesc('questions_count')
            ->get();

        return view('categories.index', compact('categories'));
    }

    /**
     * Display questions under a specific category.
     */
    public function show(string $slug, Request $request): View
    {
        $category = Category::where('slug', $slug)->orWhere('id', $slug)->firstOrFail();

        $query = Question::with(['user', 'tags', 'acceptedAnswer'])
            ->where('category_id', $category->id);

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

        return view('categories.show', compact('category', 'questions', 'sort'));
    }
}
