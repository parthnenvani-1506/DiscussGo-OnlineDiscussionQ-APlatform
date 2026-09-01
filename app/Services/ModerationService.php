<?php

namespace App\Services;

class ModerationService
{
    private array $toxicKeywords = [
        'idiot', 'stupid', 'scam', 'abuse', 'cheat', 'hack', 'warez', 'crack', 'viagra', 'casino', 'free money'
    ];

    /**
     * Check if text contains spam or offensive content.
     */
    public function checkContent(string $text): array
    {
        $clean = strtolower($text);
        $flagged = false;
        $matchedWords = [];
        $reason = null;

        // Check suspicious spam patterns (excessive links)
        $linkCount = preg_match_all('/https?:\/\/[^\s]+/i', $text, $matches);
        if ($linkCount > 4) {
            $flagged = true;
            $reason = 'spam';
        }

        // Check toxic words
        foreach ($this->toxicKeywords as $word) {
            if (preg_match('/\b' . preg_quote($word, '/') . '\b/i', $clean)) {
                $flagged = true;
                $matchedWords[] = $word;
                $reason = $reason ?? 'offensive';
            }
        }

        return [
            'flagged' => $flagged,
            'reason' => $reason,
            'matched_words' => $matchedWords,
            'score' => $flagged ? 0.85 : 0.05,
        ];
    }
}
