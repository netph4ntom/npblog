<?php

declare(strict_types=1);

namespace App\Services;

class ReadingTime
{
    public const DEFAULT_WORDS_PER_MINUTE = 200;

    /**
     * Count words in plain text or markdown by stripping markup.
     */
    public function countWords(string $content): int
    {
        $clean = $this->stripMarkdown($content);

        // Match Unicode word tokens
        if (preg_match_all('/[\p{L}\p{N}\p{M}]+/u', $clean, $matches) === false) {
            return 0;
        }

        return count($matches[0]);
    }

    /**
     * Estimate reading time in minutes based on word count.
     */
    public function estimateMinutes(string $content, int $wordsPerMinute = self::DEFAULT_WORDS_PER_MINUTE): int
    {
        $words = $this->countWords($content);

        if ($words === 0) {
            return 1;
        }

        return max(1, (int) ceil($words / $wordsPerMinute));
    }

    /**
     * Analyze content and return both word count and estimated reading time.
     *
     * @return array{word_count: int, reading_time: int}
     */
    public function analyze(string $content, int $wordsPerMinute = self::DEFAULT_WORDS_PER_MINUTE): array
    {
        $words = $this->countWords($content);
        $minutes = $words === 0 ? 1 : max(1, (int) ceil($words / $wordsPerMinute));

        return [
            'word_count' => $words,
            'reading_time' => $minutes,
        ];
    }

    /**
     * Strip markdown and HTML formatting for pure word counting.
     */
    protected function stripMarkdown(string $content): string
    {
        // Remove code blocks
        $content = preg_replace('/```[\s\S]*?```/u', ' ', $content) ?? $content;
        $content = preg_replace('/~~~[\s\S]*?~~~/u', ' ', $content) ?? $content;

        // Remove inline code
        $content = preg_replace('/`[^`]+`/u', ' ', $content) ?? $content;

        // Remove images: ![alt](url)
        $content = preg_replace('/!\[.*?\]\(.*?\)/u', ' ', $content) ?? $content;

        // Transform links to text: [text](url) -> text
        $content = preg_replace('/\[(.*?)\]\(.*?\)/u', '$1', $content) ?? $content;

        // Remove markdown headings, blockquotes, list markers
        $content = preg_replace('/^[\s>#*-+]+(?=\s)/m', ' ', $content) ?? $content;
        $content = preg_replace('/^\s*\d+\.\s+/m', ' ', $content) ?? $content;

        // Remove bold, italic, strikethrough markers
        $content = preg_replace('/[*_~]{1,3}/u', ' ', $content) ?? $content;

        // Strip any HTML tags
        $content = strip_tags($content);

        return $content;
    }
}
