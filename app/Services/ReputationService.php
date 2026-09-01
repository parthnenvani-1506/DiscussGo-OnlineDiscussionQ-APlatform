<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReputationTransaction;

class ReputationService
{
    public function __construct(
        protected BadgeService $badgeService
    ) {}

    /**
     * Award reputation points to a user.
     */
    public function award(User $user, string $reason, int $points, $reference = null): void
    {
        if ($points <= 0) {
            return;
        }

        $user->increment('reputation', $points);

        ReputationTransaction::create([
            'user_id' => $user->id,
            'points' => $points,
            'reason' => $reason,
            'reference_type' => $reference ? class_basename($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
        ]);

        $this->updateLevel($user);
        $this->badgeService->checkAndAward($user);
    }

    /**
     * Deduct reputation points from a user.
     */
    public function deduct(User $user, string $reason, int $points, $reference = null): void
    {
        if ($points <= 0) {
            return;
        }

        $newRep = max(0, $user->reputation - $points);
        $user->update(['reputation' => $newRep]);

        ReputationTransaction::create([
            'user_id' => $user->id,
            'points' => -$points,
            'reason' => $reason,
            'reference_type' => $reference ? class_basename($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
        ]);

        $this->updateLevel($user);
    }

    /**
     * Update user level according to reputation tiers.
     */
    public function updateLevel(User $user): void
    {
        $rep = $user->reputation;
        $level = 'newcomer';

        if ($rep >= 5000) {
            $level = 'mentor';
        } elseif ($rep >= 1000) {
            $level = 'expert';
        } elseif ($rep >= 500) {
            $level = 'experienced';
        } elseif ($rep >= 100) {
            $level = 'contributor';
        }

        if ($user->level !== $level) {
            $user->update(['level' => $level]);
        }
    }
}
