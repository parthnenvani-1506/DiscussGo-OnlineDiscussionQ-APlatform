<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnswerController extends Controller
{
    /**
     * Display listing of all answers with author and question info.
     */
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $status = $request->query('status');

        $query = Answer::with(['user', 'question']);

        if ($search) {
            $query->where('answer', 'LIKE', "%{$search}%");
        }

        if ($status === 'flagged') {
            $query->where('is_flagged', true);
        } elseif ($status === 'accepted') {
            $query->where('is_accepted', true);
        }

        $answers = $query->latest()->paginate(20)->withQueryString();

        return view('admin.answers.index', compact('answers', 'search', 'status'));
    }

    /**
     * Delete an answer from the platform.
     */
    public function destroy(Answer $answer): RedirectResponse
    {
        $adminId = session('admin_id');
        $question = $answer->question;

        if ($question && $question->answer_count > 0) {
            $question->decrement('answer_count');
        }

        AuditLog::create([
            'admin_id' => $adminId,
            'action' => 'delete_answer',
            'details' => "Deleted answer #{$answer->id} by user #{$answer->user_id}",
        ]);

        $answer->delete();

        return back()->with('success', 'Answer has been deleted.');
    }
}
