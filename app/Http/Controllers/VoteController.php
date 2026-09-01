<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Question;
use App\Models\Vote;
use App\Services\BadgeService;
use App\Services\NotificationService;
use App\Services\ReputationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoteController extends Controller
{
    public function __construct(
        protected ReputationService $reputationService,
        protected BadgeService $badgeService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Handle AJAX voting on Questions and Answers.
     */
    public function vote(Request $request): JsonResponse
    {
        $request->validate([
            'votable_type' => 'required|in:question,answer',
            'votable_id' => 'required|integer',
            'value' => 'required|in:1,-1',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $type = $request->votable_type;
        $votableId = (int) $request->votable_id;
        $value = (int) $request->value;

        $votable = ($type === 'question')
            ? Question::findOrFail($votableId)
            : Answer::findOrFail($votableId);

        // Prevent voting on own content
        if ($votable->user_id === $user->id) {
            return response()->json(['error' => 'You cannot vote on your own ' . $type . '.'], 422);
        }

        $author = $votable->user;

        // Check if existing vote exists
        $existingVote = Vote::where('user_id', $user->id)
            ->where('votable_type', $type)
            ->where('votable_id', $votableId)
            ->first();

        $userVote = 0;

        if ($existingVote) {
            if ($existingVote->value === $value) {
                // Remove vote (undo)
                $existingVote->delete();
                $userVote = 0;

                // Revert reputation
                if ($value === 1) {
                    $points = ($type === 'answer') ? 10 : 5;
                    $this->reputationService->deduct($author, 'Upvote removed on ' . $type, $points);
                } else {
                    $this->reputationService->award($author, 'Downvote removed on ' . $type, 2);
                    $this->reputationService->award($user, 'Downvote undo refund', 1);
                }
            } else {
                // Switch vote (e.g. from downvote to upvote or vice-versa)
                $existingVote->update(['value' => $value]);
                $userVote = $value;

                if ($value === 1) { // Was -1, now +1
                    $points = ($type === 'answer') ? 10 : 5;
                    $this->reputationService->award($author, 'Downvote changed to upvote on ' . $type, $points + 2);
                    $this->reputationService->award($user, 'Downvote removed refund', 1);

                    // Notify upvote
                    $this->notificationService->upvoteReceived($author, $votable, $user);
                } else { // Was +1, now -1
                    $points = ($type === 'answer') ? 10 : 5;
                    $this->reputationService->deduct($author, 'Upvote changed to downvote on ' . $type, $points + 2);
                    $this->reputationService->deduct($user, 'Cast a downvote on ' . $type, 1);
                }
            }
        } else {
            // New vote
            Vote::create([
                'user_id' => $user->id,
                'votable_type' => $type,
                'votable_id' => $votableId,
                'value' => $value,
            ]);
            $userVote = $value;

            if ($value === 1) {
                $points = ($type === 'answer') ? 10 : 5;
                $this->reputationService->award($author, 'Received an upvote on ' . $type, $points, $votable);
                $this->notificationService->upvoteReceived($author, $votable, $user);
            } else {
                $this->reputationService->deduct($author, 'Received a downvote on ' . $type, 2, $votable);
                $this->reputationService->deduct($user, 'Cast a downvote on ' . $type, 1, $votable);
            }
        }

        // Recalculate total vote score
        $newScore = (int) Vote::where('votable_type', $type)
            ->where('votable_id', $votableId)
            ->sum('value');

        $votable->update(['vote_score' => $newScore]);

        // Check badge achievements
        $this->badgeService->checkAndAward($author);

        return response()->json([
            'success' => true,
            'new_score' => $newScore,
            'user_vote' => $userVote,
        ]);
    }
}
