<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TagMergeService
{
    /**
     * Normalize a string for exact comparison.
     * "laravel-framework" → "laravelframework"
     */
    public function normalize(string $text): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $text));
    }

    /**
     * Compute similarity between two strings (0.0 – 1.0).
     * Uses max(levenshtein_ratio, jaccard_token_overlap).
     */
    public function similarityScore(string $a, string $b): float
    {
        $normA = $this->normalize($a);
        $normB = $this->normalize($b);

        // Exact normalized match
        if ($normA === $normB) return 1.0;

        // Levenshtein ratio
        similar_text($normA, $normB, $levenshteinPercent);
        $levenshtein = $levenshteinPercent / 100.0;

        // Jaccard token overlap
        $tokensA = preg_split('/[-_\s]+/', strtolower($a), -1, PREG_SPLIT_NO_EMPTY);
        $tokensB = preg_split('/[-_\s]+/', strtolower($b), -1, PREG_SPLIT_NO_EMPTY);
        $intersection = count(array_intersect($tokensA, $tokensB));
        $union = count(array_unique(array_merge($tokensA, $tokensB)));
        $jaccard = $union > 0 ? $intersection / $union : 0.0;

        return max($levenshtein, $jaccard);
    }

    /**
     * Check a tag name against existing tags.
     * Returns: exact_match, canonical_tag, suggestions, is_new
     */
    public function checkTag(string $name): array
    {
        $normalizedInput = $this->normalize($name);
        $allTags = Tag::all();

        // Level 1: Exact normalized match
        foreach ($allTags as $tag) {
            if ($this->normalize($tag->name) === $normalizedInput) {
                return [
                    'exact_match'   => true,
                    'canonical_tag' => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug],
                    'suggestions'   => [],
                    'is_new'        => false,
                ];
            }
        }

        // Level 2: Near-duplicate suggestions (≥ 0.75)
        $suggestions = [];
        foreach ($allTags as $tag) {
            $score = $this->similarityScore($name, $tag->name);
            if ($score >= 0.75) {
                $suggestions[] = [
                    'tag'   => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug],
                    'score' => round($score * 100),
                ];
            }
        }

        usort($suggestions, fn($a, $b) => $b['score'] <=> $a['score']);

        if (!empty($suggestions)) {
            return [
                'exact_match'   => false,
                'canonical_tag' => null,
                'suggestions'   => array_slice($suggestions, 0, 3),
                'is_new'        => false,
            ];
        }

        return [
            'exact_match'   => false,
            'canonical_tag' => null,
            'suggestions'   => [],
            'is_new'        => true,
        ];
    }

    /**
     * Check a category name against existing categories.
     */
    public function checkCategory(string $name): array
    {
        $normalizedInput = $this->normalize($name);
        $allCategories = Category::all();

        foreach ($allCategories as $cat) {
            if ($this->normalize($cat->name) === $normalizedInput) {
                return [
                    'exact_match'        => true,
                    'canonical_category' => ['id' => $cat->id, 'name' => $cat->name, 'slug' => $cat->slug],
                    'suggestions'        => [],
                ];
            }
        }

        $suggestions = [];
        foreach ($allCategories as $cat) {
            $score = $this->similarityScore($name, $cat->name);
            if ($score >= 0.70) {
                $suggestions[] = [
                    'category' => ['id' => $cat->id, 'name' => $cat->name, 'slug' => $cat->slug],
                    'score'    => round($score * 100),
                ];
            }
        }

        usort($suggestions, fn($a, $b) => $b['score'] <=> $a['score']);

        return [
            'exact_match'        => false,
            'canonical_category' => null,
            'suggestions'        => array_slice($suggestions, 0, 3),
        ];
    }

    /**
     * Find all duplicate tag groups across all existing tags (O(n²)).
     * Groups tags where any pair has similarity >= 0.80.
     */
    public function findDuplicateTagGroups(): array
    {
        $tags = Tag::all()->toArray();
        $n = count($tags);
        $visited = [];
        $groups = [];

        for ($i = 0; $i < $n; $i++) {
            if (isset($visited[$i])) continue;

            $group = [$tags[$i]];
            $maxScore = 0;

            for ($j = $i + 1; $j < $n; $j++) {
                if (isset($visited[$j])) continue;

                $score = $this->similarityScore($tags[$i]['name'], $tags[$j]['name']);
                if ($score >= 0.80) {
                    $group[] = $tags[$j];
                    $visited[$j] = true;
                    $maxScore = max($maxScore, $score);
                }
            }

            if (count($group) > 1) {
                $visited[$i] = true;
                $groups[] = [
                    'tags'            => $group,
                    'max_similarity'  => round($maxScore * 100),
                ];
            }
        }

        usort($groups, fn($a, $b) => $b['max_similarity'] <=> $a['max_similarity']);
        return $groups;
    }

    /**
     * Merge multiple tags into one canonical tag.
     * Retags all questions, deletes duplicates, recalculates usage_count.
     */
    public function mergeTags(int $canonicalId, array $mergeIds, int $adminId): array
    {
        $canonical = Tag::findOrFail($canonicalId);
        $questionsUpdated = 0;

        DB::transaction(function () use ($canonicalId, $mergeIds, $canonical, &$questionsUpdated) {
            foreach ($mergeIds as $sourceId) {
                if ($sourceId == $canonicalId) continue;

                $sourceTag = Tag::find($sourceId);
                if (!$sourceTag) continue;

                // Get questions using the source tag
                $questionIds = DB::table('question_tag')
                    ->where('tag_id', $sourceId)
                    ->pluck('question_id');

                foreach ($questionIds as $qId) {
                    // Only insert if canonical not already attached
                    $exists = DB::table('question_tag')
                        ->where('question_id', $qId)
                        ->where('tag_id', $canonicalId)
                        ->exists();

                    if (!$exists) {
                        DB::table('question_tag')->insert([
                            'question_id' => $qId,
                            'tag_id'      => $canonicalId,
                        ]);
                        $questionsUpdated++;
                    }
                }

                // Remove source tag pivots and delete source tag
                DB::table('question_tag')->where('tag_id', $sourceId)->delete();
                $sourceTag->delete();
            }

            // Recalculate canonical usage_count
            $newCount = DB::table('question_tag')->where('tag_id', $canonicalId)->count();
            $canonical->update(['usage_count' => $newCount]);
        });

        // Log audit
        \App\Models\AuditLog::create([
            'admin_id' => $adminId,
            'action'   => 'merge_tags',
            'details'  => "Merged tags [" . implode(',', $mergeIds) . "] into canonical tag #{$canonicalId} ('{$canonical->name}'). {$questionsUpdated} questions updated.",
        ]);

        $canonical->refresh();

        return [
            'merged_count'      => count($mergeIds),
            'questions_updated' => $questionsUpdated,
            'canonical_tag'     => $canonical->toArray(),
        ];
    }
}
