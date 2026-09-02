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

class LikeController extends Controller
{
    public function __construct(
        protected ReputationService $reputationService,
        protected BadgeService $badgeService,
        protected NotificationService $notificationService
    ) {}

    /**
     * Toggle like on a question or answer (Quora-style — likes only, no downvote).
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'likeable_type' => 'required|in:question,answer',
            'likeable_id'   => 'required|integer',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        $type      = $request->likeable_type;
        $likeableId = (int) $request->likeable_id;

        $likeable = ($type === 'question')
            ? Question::findOrFail($likeableId)
            : Answer::findOrFail($likeableId);

        // Cannot like own content
        if ($likeable->user_id === $user->id) {
            return response()->json(['error' => 'You cannot like your own ' . $type . '.'], 422);
        }

        $author = $likeable->user;

        $existingLike = Vote::where('user_id', $user->id)
            ->where('votable_type', $type)
            ->where('votable_id', $likeableId)
            ->first();

        if ($existingLike) {
            // Un-like
            $existingLike->delete();

            // Revert reputation
            $points = ($type === 'answer')
                ? abs(\App\Models\ReputationSetting::pointsFor('unlike_answer', -5))
                : abs(\App\Models\ReputationSetting::pointsFor('unlike_question', -3));
            $this->reputationService->deduct($author, 'Like removed on ' . $type, $points);

            $liked = false;
        } else {
            // Like
            Vote::create([
                'user_id'      => $user->id,
                'votable_type' => $type,
                'votable_id'   => $likeableId,
                'value'        => 1,
            ]);

            // Award reputation — answers get configured points, questions get configured points
            $points = ($type === 'answer')
                ? \App\Models\ReputationSetting::pointsFor('like_answer', 5)
                : \App\Models\ReputationSetting::pointsFor('like_question', 3);
            $this->reputationService->award($author, 'Received a like on ' . $type, $points, $likeable);

            // Notify content owner
            $this->notificationService->upvoteReceived($author, $likeable, $user);

            // Check badges
            $this->badgeService->checkAndAward($author);

            $liked = true;
        }

        // Recalculate like count (vote_score = total likes)
        $newCount = Vote::where('votable_type', $type)
            ->where('votable_id', $likeableId)
            ->count();

        $likeable->update(['vote_score' => $newCount]);

        return response()->json([
            'success'   => true,
            'liked'     => $liked,
            'new_count' => $newCount,
        ]);
    }
}
