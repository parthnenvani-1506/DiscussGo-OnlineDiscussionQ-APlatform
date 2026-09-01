<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Collection;

class SimilarityService
{
    private array $stopWords = [
        'a', 'about', 'above', 'after', 'again', 'against', 'all', 'am', 'an', 'and', 'any', 'are', 'as', 'at',
        'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by',
        'can', 'could', 'did', 'do', 'does', 'doing', 'down', 'during',
        'each', 'few', 'for', 'from', 'further',
        'had', 'has', 'have', 'having', 'he', 'her', 'here', 'hers', 'herself', 'him', 'himself', 'his', 'how',
        'i', 'if', 'in', 'into', 'is', 'it', 'its', 'itself',
        'just', 'me', 'more', 'most', 'my', 'myself',
        'no', 'nor', 'not', 'now', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own',
        'same', 'should', 'so', 'some', 'such',
        'than', 'that', 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too',
        'under', 'until', 'up', 'very',
        'was', 'we', 'were', 'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'with', 'would',
        'you', 'your', 'yours', 'yourself', 'yourselves'
    ];

    /**
     * Find existing questions similar to the given title and body.
     */
    public function findSimilarQuestions(string $title, string $description = '', ?int $excludeQuestionId = null, int $limit = 5): Collection
    {
        $queryText = $title . ' ' . strip_tags($description);
        $queryTokens = $this->tokenize($queryText);

        if (empty($queryTokens)) {
            return collect();
        }

        $questionsQuery = Question::with(['user', 'category', 'tags']);
        if ($excludeQuestionId) {
            $questionsQuery->where('id', '!=', $excludeQuestionId);
        }

        $allQuestions = $questionsQuery->latest()->take(100)->get();
        if ($allQuestions->isEmpty()) {
            return collect();
        }

        // Build document corpus
        $corpus = [];
        $docTokens = [];
        foreach ($allQuestions as $q) {
            $text = $q->title . ' ' . strip_tags($q->description);
            $tokens = $this->tokenize($text);
            $docTokens[$q->id] = $tokens;
            $corpus[] = $tokens;
        }

        // Compute IDF
        $totalDocs = count($corpus) + 1;
        $idf = [];
        $allUniqueWords = array_unique(array_merge($queryTokens, ...$corpus));

        foreach ($allUniqueWords as $word) {
            $docCount = 0;
            foreach ($corpus as $doc) {
                if (in_array($word, $doc, true)) {
                    $docCount++;
                }
            }
            if (in_array($word, $queryTokens, true)) {
                $docCount++;
            }
            $idf[$word] = log(($totalDocs / ($docCount + 1))) + 1;
        }

        // Compute Query TF-IDF Vector
        $queryVector = $this->computeTfidfVector($queryTokens, $idf);

        // Compute similarity for each question
        $results = [];
        foreach ($allQuestions as $q) {
            $qVector = $this->computeTfidfVector($docTokens[$q->id], $idf);
            $similarity = $this->cosineSimilarity($queryVector, $qVector);

            if ($similarity > 0.15) { // Minimum similarity threshold
                $q->similarity_score = round($similarity * 100);
                $results[] = [
                    'question' => $q,
                    'score' => $similarity,
                ];
            }
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return collect(array_slice($results, 0, $limit))->pluck('question');
    }

    /**
     * Check if a new question is a potential duplicate (similarity >= 70%).
     */
    public function checkDuplicate(string $title, string $description = ''): array
    {
        $similar = $this->findSimilarQuestions($title, $description, null, 3);
        $topMatch = $similar->first();

        $isDuplicate = $topMatch && ($topMatch->similarity_score >= 70);

        return [
            'is_duplicate' => (bool)$isDuplicate,
            'max_score' => $topMatch ? $topMatch->similarity_score : 0,
            'similar_questions' => $similar->map(fn($q) => [
                'id' => $q->id,
                'title' => $q->title,
                'slug' => $q->slug,
                'similarity' => $q->similarity_score ?? 0,
                'url' => route('questions.show', [$q->id, $q->slug]),
            ]),
        ];
    }

    private function tokenize(string $text): array
    {
        $clean = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', ' ', $text));
        $words = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter($words, function ($w) {
            return strlen($w) > 2 && !in_array($w, $this->stopWords, true);
        }));
    }

    private function computeTfidfVector(array $tokens, array $idf): array
    {
        $tf = array_count_values($tokens);
        $totalTokens = count($tokens);
        $vector = [];

        if ($totalTokens === 0) {
            return [];
        }

        foreach ($tf as $word => $count) {
            $termFreq = $count / $totalTokens;
            $idfVal = $idf[$word] ?? 1.0;
            $vector[$word] = $termFreq * $idfVal;
        }

        return $vector;
    }

    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0.0;
        $mag1 = 0.0;
        $mag2 = 0.0;

        foreach ($vec1 as $word => $val) {
            $dotProduct += $val * ($vec2[$word] ?? 0.0);
            $mag1 += $val * $val;
        }

        foreach ($vec2 as $val) {
            $mag2 += $val * $val;
        }

        $magnitude = sqrt($mag1) * sqrt($mag2);

        if ($magnitude <= 0.0) {
            return 0.0;
        }

        return min(1.0, max(0.0, $dotProduct / $magnitude));
    }
}
