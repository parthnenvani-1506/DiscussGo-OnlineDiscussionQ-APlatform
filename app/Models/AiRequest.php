<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature',
        'input_length',
        'response_time',
        'success',
        'question_id',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'response_time' => 'float',
            'input_length' => 'integer',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
