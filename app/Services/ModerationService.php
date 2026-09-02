<?php

namespace App\Services;

class ModerationService
{
    private array $toxicKeywords = [
        'idiot', 'stupid', 'scam', 'abuse', 'cheat', 'hack', 'warez', 'crack', 'viagra', 'casino', 'free money'
    ];

    /**
     * Check if text contains spam or offensive content.
     * Score ranges:
     *   0.05 = clean
     *   0.55 = mild flag (1 keyword) — saved but sent to moderator queue
     *   0.75 = moderate flag (2 keywords) — saved but sent to moderator queue
     *   0.90 = severe flag (3+ keywords or spam) — blocked outright
     */
    public function checkContent(string $text): array
    {
        $clean = strtolower($text);
        $flagged = false;
        $matchedWords = [];
        $reason = null;

        // Check suspicious spam patterns (excessive links) — severe, block it
        $linkCount = preg_match_all('/https?:\/\/[^\s]+/i', $text, $matches);
        if ($linkCount > 4) {
            return [
                'flagged' => true,
                'reason'  => 'spam',
                'matched_words' => [],
                'score'   => 0.90,
            ];
        }

        // Check toxic keywords
        foreach ($this->toxicKeywords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $clean)) {
                $flagged = true;
                $matchedWords[] = $word;
                $reason = $reason ?? 'offensive';
            }
        }

        if (!$flagged) {
            return ['flagged' => false, 'reason' => null, 'matched_words' => [], 'score' => 0.05];
        }

        // Score based on how many keywords matched
        $count = count($matchedWords);
        $score = match(true) {
            $count >= 3 => 0.90, // severe — block
            $count === 2 => 0.75, // moderate — flag for review
            default      => 0.55, // mild — flag for review
        };

        return [
            'flagged'       => true,
            'reason'        => $reason,
            'matched_words' => $matchedWords,
            'score'         => $score,
        ];
    }
}
