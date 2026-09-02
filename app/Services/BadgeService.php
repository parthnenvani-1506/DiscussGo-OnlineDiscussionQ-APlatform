<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BadgeService
{
    // Supported condition types — each maps to a resolver closure built in evaluate()
    public const CONDITION_TYPES = [
        'min_questions'          => 'Min. questions asked',
        'min_answers'            => 'Min. answers posted',
        'min_accepted'           => 'Min. accepted answers',
        'min_upvotes_on_answers' => 'Min. total likes on answers',
        'min_reputation'         => 'Min. reputation points',
        'min_views_on_question'  => 'Min. views on any single question',
        'min_followers'          => 'Min. followers',
    ];

    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Check every badge in the DB against the user and award matching ones.
     * Fully dynamic — no hardcoded per-badge methods.
     */
    public function checkAndAward(User $user): void
    {
        // Load all badges (cached 5 min so repeated calls in one request are free)
        $badges = Cache::remember('all_badges', 300, fn () => Badge::all());

        // Pre-load already-earned badge IDs for this user to avoid N+1
        $earnedIds = $user->badges()->pluck('badge_id')->toArray();

        foreach ($badges as $badge) {
            // Skip already earned
            if (in_array($badge->id, $earnedIds)) {
                continue;
            }

            // Skip badges with no condition type set yet
            if (!$badge->condition_type) {
                continue;
            }

            if ($this->evaluate($user, $badge->condition_type, (int) $badge->condition_value)) {
                $user->badges()->attach($badge->id, ['awarded_at' => now()]);
                $this->notificationService->badgeEarned($user, $badge);
                $earnedIds[] = $badge->id; // prevent double-award in same call
            }
        }
    }

    /**
     * Evaluate a single condition against a user.
     * Returns true if the user meets or exceeds the threshold.
     */
    public function evaluate(User $user, string $conditionType, int $conditionValue): bool
    {
        return match ($conditionType) {
            'min_questions'          => $user->questions()->count() >= $conditionValue,
            'min_answers'            => $user->answers()->count() >= $conditionValue,
            'min_accepted'           => $user->answers()->where('is_accepted', true)->count() >= $conditionValue,
            'min_upvotes_on_answers' => (int) $user->answers()->sum('vote_score') >= $conditionValue,
            'min_reputation'         => $user->reputation >= $conditionValue,
            'min_views_on_question'  => $user->questions()->where('view_count', '>=', $conditionValue)->exists(),
            'min_followers'          => ($user->followers_count ?? 0) >= $conditionValue,
            default                  => false,
        };
    }

    /**
     * Flush badge cache — call after admin creates/updates/deletes a badge.
     */
    public static function flushCache(): void
    {
        Cache::forget('all_badges');
    }

    /**
     * Retroactively check all existing users against a single badge.
     * Called when admin creates or updates a badge so users who already
     * meet the condition get awarded immediately.
     */
    public function awardRetroactively(Badge $badge): int
    {
        if (!$badge->condition_type) {
            return 0;
        }

        $awarded = 0;

        // Chunk through users to avoid memory issues on large datasets
        User::chunk(100, function ($users) use ($badge, &$awarded) {
            // Get IDs of users who already have this badge
            $alreadyEarned = $badge->users()->pluck('user_id')->toArray();

            foreach ($users as $user) {
                if (in_array($user->id, $alreadyEarned)) {
                    continue;
                }
                if ($this->evaluate($user, $badge->condition_type, (int) $badge->condition_value)) {
                    $user->badges()->attach($badge->id, ['awarded_at' => now()]);
                    $this->notificationService->badgeEarned($user, $badge);
                    $awarded++;
                }
            }
        });

        return $awarded;
    }
}
