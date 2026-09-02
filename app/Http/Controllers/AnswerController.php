<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnswerRequest;
use App\Http\Requests\UpdateAnswerRequest;
use App\Models\Answer;
use App\Models\Question;
use App\Services\BadgeService;
use App\Services\ModerationService;
use App\Services\NotificationService;
use App\Services\ReputationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnswerController extends Controller
{
    public function __construct(
        protected ReputationService $reputationService,
        protected BadgeService $badgeService,
        protected NotificationService $notificationService,
        protected ModerationService $moderationService
    ) {}

    /**
     * Store a newly created answer in storage.
     */
    public function store(StoreAnswerRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $question = Question::findOrFail($request->question_id);

        // Check moderation
        $modCheck = $this->moderationService->checkContent($request->answer);
        if ($modCheck['flagged'] && $modCheck['score'] > 0.8) {
            return back()->withInput()->withErrors([
                'answer' => 'Your answer contains language that violates community guidelines (' . $modCheck['reason'] . ').',
            ]);
        }

        $answer = Answer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'answer' => $request->answer,
            'is_flagged' => ($modCheck['flagged'] && $modCheck['score'] >= 0.5),
        ]);

        // Increment answer count on question
        $question->increment('answer_count');

        // Award reputation to answer author
        $this->reputationService->award($user, 'Contributed an answer to: ' . Str::limit($question->title, 30), \App\Models\ReputationSetting::pointsFor('post_answer', 10), $answer);

        // Notify question owner if different from answerer
        if ($question->user_id !== $user->id) {
            $this->notificationService->answerPosted($question, $answer);
        }

        // Check badges for answer author
        $this->badgeService->checkAndAward($user);

        return redirect()->route('questions.show', [$question->id, $question->slug])
            ->with('success', 'Answer posted successfully! +10 reputation points awarded.');
    }

    /**
     * Show the form for editing the specified answer.
     */
    public function edit(Answer $answer): View
    {
        $this->authorize('update', $answer);

        return view('answers.edit', compact('answer'));
    }

    /**
     * Update the specified answer in storage.
     */
    public function update(UpdateAnswerRequest $request, Answer $answer): RedirectResponse
    {
        $this->authorize('update', $answer);

        // Check moderation
        $modCheck = $this->moderationService->checkContent($request->answer);
        if ($modCheck['flagged'] && $modCheck['score'] > 0.8) {
            return back()->withInput()->withErrors([
                'answer' => 'Your answer violates community guidelines (' . $modCheck['reason'] . ').',
            ]);
        }

        $answer->update([
            'answer' => $request->answer,
            'is_flagged' => ($modCheck['flagged'] && $modCheck['score'] >= 0.5),
        ]);

        return redirect()->route('questions.show', [$answer->question_id, $answer->question->slug])
            ->with('success', 'Answer updated successfully.');
    }

    /**
     * Remove the specified answer from storage.
     */
    public function destroy(Answer $answer): RedirectResponse
    {
        $this->authorize('delete', $answer);

        $question = $answer->question;
        $user = $answer->user;

        // Deduct reputation
        $this->reputationService->deduct($user, 'Deleted answer on: ' . Str::limit($question->title, 30), abs(\App\Models\ReputationSetting::pointsFor('delete_answer', -10)));

        // Decrement answer count
        if ($question->answer_count > 0) {
            $question->decrement('answer_count');
        }

        // If this answer was accepted, un-accept it
        if ($answer->is_accepted) {
            $question->update([
                'is_answered' => false,
                'accepted_answer_id' => null,
            ]);
        }

        $answer->delete();

        return redirect()->route('questions.show', [$question->id, $question->slug])
            ->with('info', 'Answer removed successfully.');
    }

    /**
     * Accept or un-accept an answer as the solution (Question owner only).
     */
    public function accept(Answer $answer): RedirectResponse
    {
        $question = $answer->question;
        $this->authorize('accept', $answer);

        $answerUser = $answer->user;

        if ($answer->is_accepted) {
            // Un-accept
            $answer->update(['is_accepted' => false]);
            $question->update([
                'is_answered' => false,
                'accepted_answer_id' => null,
            ]);
            $this->reputationService->deduct($answerUser, 'Solution un-accepted on: ' . Str::limit($question->title, 30), abs(\App\Models\ReputationSetting::pointsFor('answer_unaccepted', -50)));

            return back()->with('info', 'Answer is no longer marked as accepted solution.');
        } else {
            // Unset previous accepted answer if any
            Answer::where('question_id', $question->id)->where('is_accepted', true)->update(['is_accepted' => false]);

            $answer->update(['is_accepted' => true]);
            $question->update([
                'is_answered' => true,
                'accepted_answer_id' => $answer->id,
            ]);

            // Award +50 (or configured) reputation to the answerer
            $this->reputationService->award($answerUser, 'Solution accepted for: ' . Str::limit($question->title, 30), \App\Models\ReputationSetting::pointsFor('answer_accepted', 50), $answer);

            // Notify answer author
            $this->notificationService->answerAccepted($answer);

            // Check badges
            $this->badgeService->checkAndAward($answerUser);

            return back()->with('success', 'Answer accepted as the solution! +50 reputation awarded to the author.');
        }
    }
}
