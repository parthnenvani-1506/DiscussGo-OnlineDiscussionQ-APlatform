<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_name',
        'email',
        'password',
        'city',
        'bio',
        'profile_image',
        'reputation',
        'level',
        'role',
        'followers_count',
        'following_count',
        'warning_count',
        'is_suspended',
        'suspended_reason',
        'password_reset_required',
        'otp_code',
        'otp_expires_at',
        'otp_attempts',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at'    => 'datetime',
            'password'          => 'hashed',
            'is_suspended'      => 'boolean',
            'password_reset_required' => 'boolean',
            'reputation'        => 'integer',
            'followers_count'   => 'integer',
            'following_count'   => 'integer',
            'warning_count'     => 'integer',
            'otp_attempts'      => 'integer',
        ];
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(Bookmark::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'user_badges')->withPivot('awarded_at');
    }

    public function reputationTransactions(): HasMany
    {
        return $this->hasMany(ReputationTransaction::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function followers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    public function following(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function getProfileImageUrlAttribute(): string
    {
        if (empty($this->profile_image) || $this->profile_image === 'default_profile.png' || $this->profile_image === 'user.jpg') {
            return asset('user.jpg');
        }
        if (str_starts_with($this->profile_image, 'http')) {
            return $this->profile_image;
        }
        return asset('storage/profiles/' . $this->profile_image);
    }
}
