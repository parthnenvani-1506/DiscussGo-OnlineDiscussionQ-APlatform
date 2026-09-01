<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    /**
     * Display all tags with usage counts.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');

        $query = Tag::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        }

        $tags = $query->orderByDesc('usage_count')->paginate(30)->withQueryString();

        return view('tags.index', compact('tags', 'search'));
    }

    /**
     * Display questions attached to a specific tag.
     */
    public function show(string $slug, Request $request): View
    {
        $tag = Tag::where('slug', $slug)->orWhere('name', $slug)->firstOrFail();

        $query = $tag->questions()->with(['user', 'category', 'tags', 'acceptedAnswer']);

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

        return view('tags.show', compact('tag', 'questions', 'sort'));
    }

    /**
     * API: Autocomplete search for tags.
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        $tags = Tag::where('name', 'LIKE', "%{$q}%")
            ->orderByDesc('usage_count')
            ->take(10)
            ->get(['id', 'name', 'slug', 'usage_count']);

        return response()->json($tags);
    }
}
