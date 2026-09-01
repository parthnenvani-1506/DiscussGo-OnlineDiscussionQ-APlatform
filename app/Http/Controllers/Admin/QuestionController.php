<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuestionController extends Controller
{
    /**
     * Display listing of all questions for administrative review.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $categoryId = $request->query('category');
        $status = $request->query('status');

        $query = Question::with(['user', 'category', 'tags']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($status === 'flagged') {
            $query->where('is_flagged', true);
        } elseif ($status === 'pinned') {
            $query->where('is_pinned', true);
        }

        $questions = $query->orderByDesc('is_pinned')->latest()->paginate(20)->withQueryString();
        $categories = Category::all();

        return view('admin.questions.index', compact('questions', 'categories', 'search', 'categoryId', 'status'));
    }

    /**
     * Preview question and its answers in admin panel.
     */
    public function show(Question $question): View
    {
        $question->load(['user', 'category', 'tags', 'answers.user', 'answers.votes']);

        return view('admin.questions.show', compact('question'));
    }

    /**
     * Toggle pinned status of a question.
     */
    public function togglePin(Question $question): RedirectResponse
    {
        $adminId = session('admin_id');
        $isPinned = !$question->is_pinned;

        $question->update(['is_pinned' => $isPinned]);

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => $isPinned ? 'pin_question' : 'unpin_question',
            'details' => ($isPinned ? 'Pinned' : 'Unpinned') . " question #{$question->id} ('{$question->title}')",
        ]);

        return back()->with('success', 'Question pin status updated.');
    }

    /**
     * Delete a question from the platform.
     */
    public function destroy(Question $question): RedirectResponse
    {
        $adminId = session('admin_id');
        $title = $question->title;

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => 'delete_question',
            'details' => "Deleted question #{$question->id} ('{$title}')",
        ]);

        $question->delete();

        return redirect()->route('admin.questions.index')->with('success', "Question '{$title}' was deleted.");
    }
}
