<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Answer extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'user_id',
        'answer',
        'vote_score',
        'is_accepted',
        'is_flagged',
    ];

    protected function casts(): array
    {
        return [
            'vote_score' => 'integer',
            'is_accepted' => 'boolean',
            'is_flagged' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function userVote(?User $user): int
    {
        if (!$user) {
            return 0;
        }
        $vote = $this->votes()->where('user_id', $user->id)->first();
        return $vote ? $vote->value : 0;
    }
}
