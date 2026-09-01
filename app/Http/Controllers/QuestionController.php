<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Category;
use App\Models\Question;
use App\Models\Tag;
use App\Services\BadgeService;
use App\Services\ModerationService;
use App\Services\ReputationService;
use App\Services\SimilarityService;
use App\Services\TagMergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function __construct(
        protected ReputationService $reputationService,
        protected BadgeService $badgeService,
        protected ModerationService $moderationService,
        protected SimilarityService $similarityService
    ) {}

    /**
     * Display a listing of questions with filtering and sorting.
     */
    public function index(Request $request): View
    {
        $query = Question::with(['user', 'category', 'tags', 'acceptedAnswer']);

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category)->orWhere('id', $request->category);
            });
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Filter by status
        if ($request->status === 'answered') {
            $query->where('is_answered', true);
        } elseif ($request->status === 'unanswered') {
            $query->where('answer_count', 0);
        }

        // Sorting
        $sort = $request->query('sort', 'newest');
        switch ($sort) {
            case 'votes':
                $query->orderByDesc('vote_score')->latest();
                break;
            case 'answers':
                $query->orderByDesc('answer_count')->latest();
                break;
            case 'views':
                $query->orderByDesc('view_count')->latest();
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'newest':
            default:
                $query->latest();
                break;
        }

        $questions = $query->paginate(15)->withQueryString();
        $categories = Category::withCount('questions')->get();
        $tags = Tag::orderByDesc('usage_count')->take(20)->get();

        return view('questions.index', compact('questions', 'categories', 'tags', 'sort'));
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('questions.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(StoreQuestionRequest $request): RedirectResponse
    {
        $user = auth()->user();

        // Resolve category — use existing or create new
        $categoryId = $this->resolveCategory($request->category_id, $request->new_category_name);
        if (!$categoryId) {
            return back()->withInput()->withErrors(['category_id' => 'Please select or create a valid category.']);
        }

        // Check moderation
        $modCheck = $this->moderationService->checkContent($request->title . ' ' . ($request->description ?? ''));
        if ($modCheck['flagged'] && $modCheck['score'] > 0.8) {
            return back()->withInput()->withErrors([
                'title' => 'Your question contains language that violates community guidelines.',
            ]);
        }

        // Generate unique slug
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $counter = 1;
        while (Question::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $question = Question::create([
            'user_id'     => $user->id,
            'category_id' => $categoryId,
            'title'       => $request->title,
            'slug'        => $slug,
            'description' => $request->description ?? '',
            'is_flagged'  => ($modCheck['flagged'] && $modCheck['score'] >= 0.5),
        ]);

        // Resolve tags — mix of existing IDs + new tag names
        $tagIds = $this->resolveTags($request->tag_ids ?? [], $request->new_tags ?? []);
        if (!empty($tagIds)) {
            $question->tags()->sync($tagIds);
            Tag::whereIn('id', $tagIds)->increment('usage_count');
        }

        $this->reputationService->award($user, 'Asked a question: ' . Str::limit($question->title, 30), 5, $question);
        $this->badgeService->checkAndAward($user);

        return redirect()->route('questions.show', [$question->id, $question->slug])
            ->with('success', 'Question published! +5 reputation points awarded.');
    }

    /**
     * Display the specified question.
     */
    public function show(int $id, ?string $slug = null): View
    {
        $question = Question::with([
            'user',
            'category',
            'tags',
            'answers.user',
            'answers.votes',
            'votes',
            'bookmarks'
        ])->findOrFail($id);

        // Increment view count (session debounce to avoid inflating by refresh)
        $viewedKey = 'viewed_question_' . $question->id;
        if (!session()->has($viewedKey)) {
            $question->increment('view_count');
            session()->put($viewedKey, true);
        }

        // Sort answers: accepted answer first, then highest vote_score, then oldest
        $answers = $question->answers->sort(function ($a, $b) {
            if ($a->is_accepted !== $b->is_accepted) {
                return $b->is_accepted <=> $a->is_accepted;
            }
            if ($a->vote_score !== $b->vote_score) {
                return $b->vote_score <=> $a->vote_score;
            }
            return $a->created_at <=> $b->created_at;
        });

        // Fetch related questions using similarity
        $similarQuestions = $this->similarityService->findSimilarQuestions($question->title, $question->description ?? '', $question->id, 4);

        return view('questions.show', compact('question', 'answers', 'similarQuestions'));
    }

    /**
     * Show the form for editing the specified question.
     */
    public function edit(Question $question): View
    {
        $this->authorize('update', $question);

        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        $selectedTagIds = $question->tags->pluck('id')->toArray();

        return view('questions.edit', compact('question', 'categories', 'tags', 'selectedTagIds'));
    }

    /**
     * Update the specified question in storage.
     */
    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $this->authorize('update', $question);

        $categoryId = $this->resolveCategory($request->category_id, $request->new_category_name);
        if (!$categoryId) {
            return back()->withInput()->withErrors(['category_id' => 'Please select or create a valid category.']);
        }

        $modCheck = $this->moderationService->checkContent($request->title . ' ' . ($request->description ?? ''));
        if ($modCheck['flagged'] && $modCheck['score'] > 0.8) {
            return back()->withInput()->withErrors([
                'title' => 'Your question content violates community guidelines.',
            ]);
        }

        $question->update([
            'category_id' => $categoryId,
            'title'       => $request->title,
            'description' => $request->description ?? '',
            'is_flagged'  => ($modCheck['flagged'] && $modCheck['score'] >= 0.5),
        ]);

        $tagIds = $this->resolveTags($request->tag_ids ?? [], $request->new_tags ?? []);
        if (!empty($tagIds)) {
            $question->tags()->sync($tagIds);
        }

        return redirect()->route('questions.show', [$question->id, $question->slug])
            ->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified question from storage.
     */
    public function destroy(Question $question): RedirectResponse
    {
        $this->authorize('delete', $question);

        $user = $question->user;

        // Deduct reputation
        $this->reputationService->deduct($user, 'Deleted question: ' . Str::limit($question->title, 30), 5);

        // Decrement tag usage counts
        $tagIds = $question->tags->pluck('id');
        Tag::whereIn('id', $tagIds)->where('usage_count', '>', 0)->decrement('usage_count');

        $question->delete();

        return redirect()->route('questions.index')->with('info', 'Question removed successfully.');
    }

    /**
     * API: Get related questions.
     */
    public function related(int $id): JsonResponse
    {
        $question = Question::findOrFail($id);
        $similar = $this->similarityService->findSimilarQuestions($question->title, $question->description ?? '', $question->id, 4);

        return response()->json($similar);
    }

    /**
     * Resolve category — use existing ID or find/create by name.
     */
    private function resolveCategory(?int $categoryId, ?string $newName): ?int
    {
        if ($categoryId) {
            return $categoryId;
        }

        if (!$newName) return null;

        $newName = trim($newName);

        // 1. Exact match (case-insensitive)
        $existing = Category::whereRaw('LOWER(name) = ?', [strtolower($newName)])->first();
        if ($existing) return $existing->id;

        // 2. Similar match via TagMergeService
        $mergeService = app(\App\Services\TagMergeService::class);
        $result = $mergeService->checkCategory($newName);
        if ($result['exact_match'] && isset($result['canonical_category'])) {
            return $result['canonical_category']['id'];
        }
        if (!empty($result['suggestions']) && $result['suggestions'][0]['score'] >= 80) {
            return $result['suggestions'][0]['category']['id'];
        }

        // 3. Create new category
        $slug = \Illuminate\Support\Str::slug($newName);
        $slugBase = $slug;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $i++;
        }

        $category = Category::create([
            'name'        => $newName,
            'slug'        => $slug,
            'description' => "Discussions about {$newName}.",
            'color'       => '#2563eb',
            'icon'        => 'bi bi-folder',
        ]);

        return $category->id;
    }

    /**
     * Resolve tags — use existing IDs + create new unique tags.
     */
    private function resolveTags(array $existingIds, array $newTagNames): array
    {
        $allIds = array_filter(array_map('intval', $existingIds));
        $mergeService = app(\App\Services\TagMergeService::class);

        foreach ($newTagNames as $name) {
            $name = trim(strtolower($name));
            if (!$name) continue;

            $result = $mergeService->checkTag($name);

            if ($result['exact_match'] && isset($result['canonical_tag'])) {
                // Use existing
                $allIds[] = $result['canonical_tag']['id'];
            } elseif (!empty($result['suggestions']) && $result['suggestions'][0]['score'] >= 80) {
                // Use best match
                $allIds[] = $result['suggestions'][0]['tag']['id'];
            } else {
                // Create new tag
                $slug = \Illuminate\Support\Str::slug($name);
                $slugBase = $slug;
                $i = 1;
                while (Tag::where('slug', $slug)->exists()) {
                    $slug = $slugBase . '-' . $i++;
                }
                $tag = Tag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'slug' => $slug, 'description' => "Questions tagged {$name}.", 'usage_count' => 0]
                );
                $allIds[] = $tag->id;
            }
        }

        return array_unique(array_slice($allIds, 0, 5));
    }
}
