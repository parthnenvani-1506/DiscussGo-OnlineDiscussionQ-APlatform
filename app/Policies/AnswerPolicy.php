<?php

namespace App\Policies;

use App\Models\Answer;
use App\Models\User;

class AnswerPolicy
{
    /**
     * Determine whether the user can update the answer.
     */
    public function update(User $user, Answer $answer): bool
    {
        return $user->id === $answer->user_id;
    }

    /**
     * Determine whether the user can delete the answer.
     */
    public function delete(User $user, Answer $answer): bool
    {
        return $user->id === $answer->user_id;
    }

    /**
     * Determine whether the user can mark an answer as accepted.
     */
    public function accept(User $user, Answer $answer): bool
    {
        // Only question author can accept, and cannot accept own answer (or if allowed, owner match)
        return $user->id === $answer->question->user_id;
    }
}
