<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ReputationService;
use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FollowController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
        protected ReputationService $reputationService,
        protected BadgeService $badgeService
    ) {}

    /**
     * Toggle follow/unfollow a user via AJAX.
     */
    public function toggle(int $userId): JsonResponse
    {
        $authUser = auth()->user();

        if ($authUser->id === $userId) {
            return response()->json(['error' => 'You cannot follow yourself.'], 422);
        }

        $target = User::findOrFail($userId);

        $existing = Follow::where('follower_id', $authUser->id)
            ->where('following_id', $userId)
            ->first();

        if ($existing) {
            // Unfollow
            $existing->delete();
            $target->decrement('followers_count');
            $authUser->decrement('following_count');

            return response()->json([
                'following' => false,
                'followers_count' => max(0, $target->fresh()->followers_count),
            ]);
        } else {
            // Follow
            Follow::create([
                'follower_id' => $authUser->id,
                'following_id' => $userId,
            ]);
            $target->increment('followers_count');
            $authUser->increment('following_count');

            // Award +2 reputation to the target
            $this->reputationService->award($target, 'Gained a new follower', 2);

            // Notify target
            $this->notificationService->userFollowed($target, $authUser);

            // Check badges
            $this->badgeService->checkAndAward($target);

            return response()->json([
                'following' => true,
                'followers_count' => $target->fresh()->followers_count,
            ]);
        }
    }

    /**
     * Show followers list for a user.
     */
    public function followers(int $id): View
    {
        $user = User::findOrFail($id);
        $followers = $user->followers()->paginate(20);
        return view('follows.followers', compact('user', 'followers'));
    }

    /**
     * Show following list for a user.
     */
    public function following(int $id): View
    {
        $user = User::findOrFail($id);
        $following = $user->following()->paginate(20);
        return view('follows.following', compact('user', 'following'));
    }
}
