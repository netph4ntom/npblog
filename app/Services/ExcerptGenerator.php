<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

class ExcerptGenerator
{
    public const MAX_EXCERPT_LENGTH = 300;

    public const AUTO_FALLBACK_TARGET = 160;

    /**
     * Generate an excerpt from manual input or automatically from the body.
     */
    public function generate(?string $excerpt, string $body, int $maxLength = self::MAX_EXCERPT_LENGTH): string
    {
        if ($excerpt !== null && trim($excerpt) !== '') {
            $cleaned = trim(preg_replace('/\s+/u', ' ', $excerpt) ?? $excerpt);

            if (mb_strlen($cleaned) <= $maxLength) {
                return $cleaned;
            }

            return Str::limit($cleaned, max(0, $maxLength - 3), '...');
        }

        if (trim($body) === '') {
            return '';
        }

        return $this->extractFromBody($body, $maxLength);
    }

    /**
     * Extract a clean summary from the first meaningful paragraph of the body.
     */
    protected function extractFromBody(string $body, int $maxLength): string
    {
        // Split into paragraph blocks separated by 2 or more newlines
        $paragraphs = preg_split('/\R{2,}/u', trim($body));

        if (! is_array($paragraphs) || empty($paragraphs)) {
            return '';
        }

        $chosen = '';

        foreach ($paragraphs as $para) {
            $trimmed = trim($para);
            // Skip headings (# Header), horizontal rules (---, ***), images (![alt](url)), code blocks
            if ($trimmed === '' || str_starts_with($trimmed, '#') || str_starts_with($trimmed, '```') || str_starts_with($trimmed, '~~~') || preg_match('/^!\[.*?\]\(.*?\)$/u', $trimmed)) {
                continue;
            }

            $chosen = $trimmed;
            break;
        }

        if ($chosen === '') {
            // Fallback: take first paragraph regardless if none matched
            $chosen = trim($paragraphs[0]);
        }

        // Clean markdown and HTML markup
        $plain = $this->stripFormatting($chosen);

        if ($plain === '') {
            return '';
        }

        $targetLength = min(self::AUTO_FALLBACK_TARGET, $maxLength);

        if (mb_strlen($plain) <= $targetLength) {
            return $plain;
        }

        return Str::limit($plain, max(0, $targetLength - 3), '...');
    }

    /**
     * Strip markdown and HTML tags for excerpt text.
     */
    protected function stripFormatting(string $text): string
    {
        // Remove markdown images
        $text = preg_replace('/!\[.*?\]\(.*?\)/u', '', $text) ?? $text;

        // Convert links to text
        $text = preg_replace('/\[(.*?)\]\(.*?\)/u', '$1', $text) ?? $text;

        // Remove markdown headings
        $text = preg_replace('/^#+\s+/mu', '', $text) ?? $text;

        // Remove inline code
        $text = preg_replace('/`([^`]+)`/u', '$1', $text) ?? $text;

        // Remove bold, italic, strikethrough markers
        $text = preg_replace('/[*_~]{1,3}/u', '', $text) ?? $text;

        // Strip HTML
        $text = strip_tags($text);

        // Collapse multiple whitespaces
        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }
}
