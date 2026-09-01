<?php

namespace App\Services;

use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Badge;
use App\Models\Notification;

class NotificationService
{
    public function answerPosted(Question $question, Answer $answer): void
    {
        if ($question->user_id === $answer->user_id) {
            return;
        }

        $this->create($question->user, 'answer_posted', [
            'message' => "{$answer->user->user_name} answered your question: '{$question->title}'",
            'question_id' => $question->id,
            'question_slug' => $question->slug,
            'answer_id' => $answer->id,
            'actor_name' => $answer->user->user_name,
            'actor_avatar' => $answer->user->profile_image_url,
        ]);
    }

    public function answerAccepted(Answer $answer): void
    {
        $question = $answer->question;
        $this->create($answer->user, 'answer_accepted', [
            'message' => "Your answer on '{$question->title}' was marked as accepted! (+50 Rep)",
            'question_id' => $question->id,
            'question_slug' => $question->slug,
            'answer_id' => $answer->id,
            'actor_name' => $question->user->user_name,
            'actor_avatar' => $question->user->profile_image_url,
        ]);
    }

    public function upvoteReceived(User $recipient, $votable, ?User $voter = null): void
    {
        if ($voter && $recipient->id === $voter->id) {
            return;
        }

        $type = class_basename($votable) === 'Question' ? 'question' : 'answer';
        $title = $type === 'question' ? $votable->title : $votable->question->title;
        $slug = $type === 'question' ? $votable->slug : $votable->question->slug;
        $voterName = $voter ? $voter->user_name : 'Someone';

        $this->create($recipient, 'upvote_received', [
            'message' => "{$voterName} upvoted your {$type} on '{$title}'. (+10 Rep)",
            'question_slug' => $slug,
            'actor_name' => $voterName,
            'actor_avatar' => $voter?->profile_image_url ?? null,
        ]);
    }

    public function badgeEarned(User $user, Badge $badge): void
    {
        $this->create($user, 'badge_earned', [
            'message' => "Congratulations! You earned the '{$badge->name}' badge {$badge->icon}",
            'badge_id' => $badge->id,
            'badge_name' => $badge->name,
            'badge_icon' => $badge->icon,
        ]);
    }

    public function userFollowed(User $followed, User $follower): void
    {
        $this->create($followed, 'user_followed', [
            'message' => "{$follower->user_name} started following you.",
            'actor_name' => $follower->user_name,
            'actor_id' => $follower->id,
            'actor_avatar' => $follower->profile_image_url,
        ]);
    }

    public function contentRemoved(User $owner, string $type, string $reason): void
    {
        $this->create($owner, 'content_removed', [
            'message' => "Your {$type} was removed by a moderator. Reason: {$reason}",
            'type' => $type,
            'reason' => $reason,
        ]);
    }

    public function userWarned(User $user, string $reason): void
    {
        $this->create($user, 'user_warned', [
            'message' => "You have received a moderator warning: {$reason}",
            'reason' => $reason,
        ]);
    }

    public function userSuspended(User $user, string $reason): void
    {
        $this->create($user, 'user_suspended', [
            'message' => "Your account has been suspended. Reason: {$reason}",
            'reason' => $reason,
        ]);
    }

    private function create(User $user, string $type, array $data): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
        ]);
    }
}
