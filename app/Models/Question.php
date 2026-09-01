<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'view_count',
        'vote_score',
        'answer_count',
        'bookmark_count',
        'is_answered',
        'accepted_answer_id',
        'ai_summary',
        'ai_summary_at',
        'is_flagged',
        'is_featured',
        'is_pinned',
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'vote_score' => 'integer',
            'answer_count' => 'integer',
            'bookmark_count' => 'integer',
            'is_answered' => 'boolean',
            'is_flagged' => 'boolean',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'ai_summary_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($question) {
            if (empty($question->slug)) {
                $baseSlug = Str::slug($question->title);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $count;
                    $count++;
                }
                $question->slug = $slug;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function acceptedAnswer(): BelongsTo
    {
        return $this->belongsTo(Answer::class, 'accepted_answer_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'question_tag');
    }

    public function votes(): MorphMany
    {
        return $this->morphMany(Vote::class, 'votable');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function isBookmarkedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->bookmarks()->where('user_id', $user->id)->exists();
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
