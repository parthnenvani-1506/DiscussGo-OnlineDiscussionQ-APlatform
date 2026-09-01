<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TagExtractionService
{
    /**
     * Suggest relevant tags based on title and description.
     */
    public function suggestTags(string $title, string $description = '', int $limit = 5): Collection
    {
        $text = strtolower($title . ' ' . strip_tags($description));
        $allTags = Tag::all();

        $scoredTags = [];

        foreach ($allTags as $tag) {
            $tagName = strtolower($tag->name);
            $tagSlug = strtolower($tag->slug);
            $score = 0;

            // Direct word match in title (high weight)
            if (preg_match('/\b' . preg_quote($tagName, '/') . '\b/i', $title)) {
                $score += 10;
            } elseif (str_contains(strtolower($title), $tagName)) {
                $score += 5;
            }

            // Direct word match in body
            if (preg_match('/\b' . preg_quote($tagName, '/') . '\b/i', $text)) {
                $score += 4;
            }

            // Substring or slug match
            if ($tagName !== $tagSlug && str_contains($text, $tagSlug)) {
                $score += 3;
            }

            if ($score > 0) {
                // Boost popular tags slightly
                $score += min(2, $tag->usage_count / 10);
                $scoredTags[] = [
                    'tag' => $tag,
                    'score' => $score,
                ];
            }
        }

        usort($scoredTags, fn($a, $b) => $b['score'] <=> $a['score']);

        return collect(array_slice($scoredTags, 0, $limit))->pluck('tag');
    }
}
