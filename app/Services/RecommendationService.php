<?php

namespace App\Services;

use App\Models\User;
use App\Models\Question;
use Illuminate\Pagination\LengthAwarePaginator;

class RecommendationService
{
    /**
     * Get personalized feed of questions for a logged-in user.
     */
    public function getPersonalizedFeed(?User $user, int $perPage = 15): LengthAwarePaginator
    {
        if (!$user) {
            return Question::with(['user', 'category', 'tags', 'acceptedAnswer'])
                ->latest()
                ->paginate($perPage);
        }

        // Get categories and tags the user has interacted with (asked or answered)
        $userQuestionCatIds = $user->questions()->pluck('category_id')->toArray();
        $userAnswerCatIds = Question::whereIn('id', $user->answers()->pluck('question_id'))->pluck('category_id')->toArray();
        $preferredCatIds = array_unique(array_merge($userQuestionCatIds, $userAnswerCatIds));

        $userTagIds = $user->questions()->with('tags')->get()->pluck('tags')->flatten()->pluck('id')->toArray();

        $query = Question::with(['user', 'category', 'tags', 'acceptedAnswer']);

        if (!empty($preferredCatIds) || !empty($userTagIds)) {
            // Build a scoring expression using safe integer IDs (never user input)
            $catIdList = empty($preferredCatIds) ? '0' : implode(',', array_map('intval', $preferredCatIds));
            $query->orderByRaw("
                (CASE WHEN category_id IN ({$catIdList}) THEN 5 ELSE 0 END)
                + (vote_score * 0.5)
                + (answer_count * 1.5) DESC
            ")->orderByDesc('created_at');
        } else {
            $query->orderByDesc('vote_score')->orderByDesc('created_at');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get questions from users that the given user follows.
     */
    public function getFollowingFeed(User $user, int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        $followingIds = $user->following()->pluck('users.id')->toArray();

        if (empty($followingIds)) {
            return Question::with(['user', 'category', 'tags', 'acceptedAnswer'])
                ->whereIn('user_id', [0]) // empty result
                ->paginate($perPage);
        }

        return Question::with(['user', 'category', 'tags', 'acceptedAnswer'])
            ->whereIn('user_id', $followingIds)
            ->latest()
            ->paginate($perPage);
    }
    public function getTrending(int $limit = 5)
    {
        return Question::with(['user', 'category', 'tags'])
            ->where('created_at', '>=', now()->subDays(14))
            ->orderByDesc('vote_score')
            ->orderByDesc('answer_count')
            ->take($limit)
            ->get();
    }
}
