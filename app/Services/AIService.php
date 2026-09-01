<?php

namespace App\Services;

use App\Models\Question;
use App\Models\AiRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $baseUrl;
    private string $model;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = env('OLLAMA_BASE_URL', 'http://localhost:11434');
        $this->model = env('OLLAMA_MODEL', 'llama3.2:3b');
        $this->timeout = (int)env('OLLAMA_TIMEOUT', 10);
    }

    /**
     * Check if local Ollama server is running and accessible.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/tags");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Summarize answers for a question using local LLM or fallback heuristics.
     */
    public function summarizeAnswers(Question $question): string
    {
        $answers = $question->answers()->with('user')->orderByDesc('vote_score')->get();
        if ($answers->isEmpty()) {
            return "No answers have been posted for this question yet.";
        }

        $startTime = microtime(true);
        $promptText = "Question: " . $question->title . "\n";
        $promptText .= "Details: " . strip_tags($question->description) . "\n\n";
        $promptText .= "Community Answers:\n";

        foreach ($answers as $index => $ans) {
            $num = $index + 1;
            $score = $ans->vote_score;
            $isAccepted = $ans->is_accepted ? " [ACCEPTED ANSWER]" : "";
            $promptText .= "Answer #{$num} (Score: {$score}{$isAccepted}): " . strip_tags($ans->answer) . "\n\n";
        }

        $prompt = "You are an expert AI developer assistant on DiscussHub. Provide a concise, clear summary (max 3-4 sentences) highlighting the most agreed-upon community solution and key recommendations from the answers below.\n\n" . $promptText;

        // Attempt Ollama directly — no pre-flight isAvailable() check
        // isAvailable() uses a 2s timeout that fails when the model is cold-loading
        try {
            $response = Http::connectTimeout(5)->timeout($this->timeout)->post("{$this->baseUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

            if ($response->successful() && !empty($response->json('response'))) {
                $summary = trim($response->json('response'));
                $this->logRequest('summarization', strlen($prompt), microtime(true) - $startTime, true, $question->id);

                $question->update([
                    'ai_summary' => $summary,
                    'ai_summary_at' => now(),
                ]);

                return $summary;
            }
        } catch (\Exception $e) {
            Log::warning("Ollama summarizeAnswers failed, using heuristic fallback: " . $e->getMessage());
        }

        // Fallback Heuristic Extractive Summary
        $topAnswer = $question->acceptedAnswer ?? $answers->first();
        $fallbackSummary = "Community Consensus: The top-recommended solution (by {$topAnswer->user->user_name} with +{$topAnswer->vote_score} score) recommends: " .
            \Illuminate\Support\Str::limit(strip_tags($topAnswer->answer), 220);

        $this->logRequest('summarization', strlen($prompt), microtime(true) - $startTime, true, $question->id, 'Fallback Heuristic');

        $question->update([
            'ai_summary' => $fallbackSummary,
            'ai_summary_at' => now(),
        ]);

        return $fallbackSummary;
    }

    /**
     * Generate a draft answer for a question using local LLM.
     */
    public function generateAnswer(\App\Models\Question $question): string
    {
        $startTime = microtime(true);

        $topAnswers = $question->answers ?? collect();
        $contextText = '';
        foreach ($topAnswers->take(3) as $i => $ans) {
            $contextText .= "Existing Answer #" . ($i + 1) . ": " . strip_tags($ans->answer) . "\n\n";
        }

        $prompt = "You are a helpful assistant for a Q&A community platform.\n"
            . "Based on the following question and existing community answers, write a helpful, accurate draft answer.\n"
            . "Keep it concise and informative (2-4 paragraphs). Do not copy existing answers verbatim.\n\n"
            . "Question: " . $question->title . "\n\n"
            . "Context: " . strip_tags($question->description) . "\n\n"
            . ($contextText ? "Top existing answers for context:\n{$contextText}" : "")
            . "Write a new helpful answer:";

        // Attempt Ollama directly — no pre-flight isAvailable() check
        try {
            $response = Http::connectTimeout(5)->timeout($this->timeout)->post("{$this->baseUrl}/api/generate", [
                'model'  => $this->model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

            if ($response->successful() && !empty($response->json('response'))) {
                $draft = trim($response->json('response'));
                $this->logRequest('answer_generation', strlen($prompt), microtime(true) - $startTime, true, $question->id);
                return $draft;
            }
        } catch (\Exception $e) {
            Log::warning("Ollama generateAnswer failed, using fallback: " . $e->getMessage());
        }

        // Fallback
        $fallback = "Based on the question \"{$question->title}\", here is a helpful starting point:\n\n"
            . "Consider addressing the core issue directly with a clear explanation. "
            . "Provide relevant examples or code snippets where applicable. "
            . "Reference any official documentation that supports your answer.\n\n"
            . "Please edit this draft with your actual knowledge before submitting.";

        $this->logRequest('answer_generation', strlen($prompt), microtime(true) - $startTime, true, $question->id, 'Fallback draft');
        return $fallback;
    }

    /**
     * Check Question Quality Score & generate helpful tips.
     */
    public function evaluateQuestionQuality(string $title, string $description): array
    {
        $score = 50;
        $tips = [];
        $checks = [];

        // Title length check
        $titleLen = strlen(trim($title));
        if ($titleLen >= 25 && $titleLen <= 150) {
            $score += 20;
            $checks[] = "Clear and descriptive title length";
        } elseif ($titleLen < 20) {
            $tips[] = "Title is quite brief. Include specific details like framework version or error name.";
        }

        // Question mark in title
        if (str_ends_with(trim($title), '?')) {
            $score += 5;
            $checks[] = "Proper question phrasing";
        }

        // Description length & detail check
        $descLen = strlen(strip_tags(trim($description)));
        if ($descLen >= 100) {
            $score += 20;
            $checks[] = "Detailed problem description provided";
        } elseif ($descLen < 50) {
            $tips[] = "Explain what you tried and what expected outcome you are looking for.";
        }

        // Code block presence check
        if (str_contains($description, '<code>') || str_contains($description, '<pre>')) {
            $score += 10;
            $checks[] = "Includes code snippets or logs";
        } else {
            $tips[] = "Consider adding relevant code snippets or error stack traces.";
        }

        $score = min(100, max(20, $score));

        return [
            'score' => $score,
            'checks' => $checks,
            'tips' => $tips,
        ];
    }

    /**
     * Log an AI request to the database.
     */
    public function logRequest(string $feature, ?int $inputLength, float $responseTime, bool $success, ?int $questionId = null, ?string $details = null): void
    {
        try {
            AiRequest::create([
                'feature' => $feature,
                'input_length' => $inputLength,
                'response_time' => round($responseTime, 3),
                'success' => $success,
                'question_id' => $questionId,
                'details' => $details,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to log AI request: " . $e->getMessage());
        }
    }
}
