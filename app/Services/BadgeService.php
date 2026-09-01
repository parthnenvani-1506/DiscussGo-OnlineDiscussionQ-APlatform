<?php

namespace App\Services;

use App\Models\User;
use App\Models\Badge;

class BadgeService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Check all badge criteria for a user and award if eligible.
     */
    public function checkAndAward(User $user): void
    {
        $this->checkFirstQuestion($user);
        $this->checkFirstAnswer($user);
        $this->checkHelpful($user);
        $this->checkPopularQuestion($user);
        $this->checkAcceptedAnswer($user);
        $this->checkReputation1000($user);
        $this->checkTop50Answers($user);
        $this->checkWellFollowed($user);
    }

    private function awardBadge(User $user, string $criteria): void
    {
        $badge = Badge::where('criteria', $criteria)->first();
        if ($badge && !$user->badges()->where('badge_id', $badge->id)->exists()) {
            $user->badges()->attach($badge->id, ['awarded_at' => now()]);
            $this->notificationService->badgeEarned($user, $badge);
        }
    }

    private function checkFirstQuestion(User $user): void
    {
        if ($user->questions()->count() >= 1) {
            $this->awardBadge($user, 'first_question');
        }
    }

    private function checkFirstAnswer(User $user): void
    {
        if ($user->answers()->count() >= 1) {
            $this->awardBadge($user, 'first_answer');
        }
    }

    private function checkHelpful(User $user): void
    {
        $totalUpvotes = $user->answers()->sum('vote_score');
        if ($totalUpvotes >= 10) {
            $this->awardBadge($user, 'helpful_10_upvotes');
        }
    }

    private function checkPopularQuestion(User $user): void
    {
        if ($user->questions()->where('view_count', '>=', 100)->exists()) {
            $this->awardBadge($user, 'popular_100_views');
        }
    }

    private function checkAcceptedAnswer(User $user): void
    {
        if ($user->answers()->where('is_accepted', true)->exists()) {
            $this->awardBadge($user, 'first_accepted_answer');
        }
    }

    private function checkReputation1000(User $user): void
    {
        if ($user->reputation >= 1000) {
            $this->awardBadge($user, 'reputation_1000');
        }
    }

    private function checkTop50Answers(User $user): void
    {
        if ($user->answers()->count() >= 50) {
            $this->awardBadge($user, 'top_50_answers');
        }
    }

    private function checkWellFollowed(User $user): void
    {
        if ($user->followers_count >= 10) {
            $this->awardBadge($user, 'well_followed');
        }
    }
}
