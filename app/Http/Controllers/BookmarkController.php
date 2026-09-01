<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookmarkController extends Controller
{
    /**
     * Display a listing of user's saved/bookmarked questions.
     */
    public function index(): View
    {
        $user = auth()->user();

        $bookmarks = Bookmark::with(['question.user', 'question.category', 'question.tags', 'question.acceptedAnswer'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return view('bookmarks.index', compact('bookmarks'));
    }

    /**
     * Toggle bookmark state for a question via AJAX.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $questionId = (int) $request->question_id;
        $question = Question::findOrFail($questionId);

        $existing = Bookmark::where('user_id', $user->id)
            ->where('question_id', $questionId)
            ->first();

        if ($existing) {
            $existing->delete();
            if ($question->bookmark_count > 0) {
                $question->decrement('bookmark_count');
            }
            $bookmarked = false;
        } else {
            Bookmark::create([
                'user_id' => $user->id,
                'question_id' => $questionId,
            ]);
            $question->increment('bookmark_count');
            $bookmarked = true;
        }

        $count = $question->bookmarks()->count();

        return response()->json([
            'success' => true,
            'bookmarked' => $bookmarked,
            'count' => $count,
        ]);
    }
}
