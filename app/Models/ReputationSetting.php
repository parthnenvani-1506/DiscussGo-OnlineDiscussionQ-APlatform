<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ReputationSetting extends Model
{
    protected $fillable = ['key', 'label', 'group', 'points', 'description'];

    protected function casts(): array
    {
        return ['points' => 'integer'];
    }

    /**
     * Get the points for a given key, falling back to a default if the key
     * is not yet in the database (safety net during migrations).
     */
    public static function pointsFor(string $key, int $default = 0): int
    {
        $settings = Cache::remember('reputation_settings', 300, function () {
            return static::all()->keyBy('key');
        });

        return $settings->has($key) ? (int) $settings->get($key)->points : $default;
    }

    /**
     * Flush the settings cache — call after every admin save.
     */
    public static function flushCache(): void
    {
        Cache::forget('reputation_settings');
    }
}
