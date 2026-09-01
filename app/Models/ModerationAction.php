<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'moderator_id', 'action_type', 'target_type', 'target_id',
        'reason', 'report_id', 'ai_flag_source',
    ];

    protected function casts(): array
    {
        return ['ai_flag_source' => 'boolean'];
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }
}
