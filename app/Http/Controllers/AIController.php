<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\AIService;
use App\Services\SimilarityService;
use App\Services\TagExtractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function __construct(
        protected AIService $aiService,
        protected SimilarityService $similarityService,
        protected TagExtractionService $tagExtractionService
    ) {}

    /**
     * Check if a proposed question is a duplicate or find similar existing questions.
     */
    public function checkDuplicate(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|min:5',
            'description' => 'nullable|string',
        ]);

        $result = $this->similarityService->checkDuplicate($request->title, $request->description ?? '');

        return response()->json($result);
    }

    /**
     * Suggest relevant tags based on title and description content.
     */
    public function suggestTags(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|min:5',
            'description' => 'nullable|string',
        ]);

        $tags = $this->tagExtractionService->suggestTags($request->title, $request->description ?? '', 5);

        return response()->json([
            'tags' => $tags->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'slug' => $t->slug,
            ]),
        ]);
    }

    /**
     * Summarize answers for a question using local Ollama LLM or heuristic fallback.
     */
    public function summarize(int $id): JsonResponse
    {
        // Allow up to 120 seconds — Ollama can be slow on first cold load
        set_time_limit(120);

        $question = Question::with(['answers.user'])->findOrFail($id);

        if ($question->answers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No answers to summarize yet.',
            ], 422);
        }

        // Return cached summary if available and ?force not set
        if ($question->ai_summary && $question->ai_summary_at && !request()->boolean('force')) {
            return response()->json([
                'success'      => true,
                'summary'      => $question->ai_summary,
                'generated_at' => $question->ai_summary_at->diffForHumans(),
                'cached'       => true,
            ]);
        }

        $summary = $this->aiService->summarizeAnswers($question);

        return response()->json([
            'success'      => true,
            'summary'      => $summary,
            'generated_at' => now()->diffForHumans(),
            'cached'       => false,
        ]);
    }

    /**
     * Evaluate question quality score and return helpful real-time writing tips.
     */
    public function evaluateQuality(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $result = $this->aiService->evaluateQuestionQuality($request->title, $request->description ?? '');

        return response()->json($result);
    }

    /**
     * Generate a draft answer using local Ollama LLM.
     */
    public function generateAnswer(int $id): JsonResponse
    {
        // Allow up to 120 seconds — Ollama can be slow on first cold load
        set_time_limit(120);

        $question = Question::with(['answers' => fn($q) => $q->orderByDesc('vote_score')->take(3)])->findOrFail($id);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required.'], 401);
        }

        $draft = $this->aiService->generateAnswer($question);

        return response()->json([
            'success' => true,
            'draft'   => $draft,
        ]);
    }
}
