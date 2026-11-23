<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Markdown\HighlightedCodeRenderer;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class MarkdownRenderer
{
    protected MarkdownConverter $converter;

    protected HtmlSanitizer $sanitizer;

    public function __construct(
        ?MarkdownConverter $converter = null,
        ?HtmlSanitizer $sanitizer = null
    ) {
        $this->converter = $converter ?? $this->createConverter();
        $this->sanitizer = $sanitizer ?? $this->createSanitizer();
    }

    /**
     * Render Markdown to sanitized HTML.
     */
    public function render(string $markdown): string
    {
        $rawHtml = (string) $this->converter->convert($markdown);

        return $this->sanitizer->sanitize($rawHtml);
    }

    /**
     * Create and configure the CommonMark converter.
     */
    protected function createConverter(): MarkdownConverter
    {
        $config = [
            'html_input' => 'allow',
            'allow_unsafe_links' => true,
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'id_prefix' => '',
                'fragment_prefix' => '',
                'insert' => 'after',
                'title' => 'Permalink',
                'symbol' => '#',
                'apply_id_to_heading' => true,
                'aria_hidden' => false,
            ],
            'slug_normalizer' => [
                'max_length' => 100,
            ],
        ];

        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension);
        $environment->addExtension(new GithubFlavoredMarkdownExtension);
        $environment->addExtension(new HeadingPermalinkExtension);

        $codeRenderer = new HighlightedCodeRenderer;
        $environment->addRenderer(FencedCode::class, $codeRenderer, 100);
        $environment->addRenderer(IndentedCode::class, $codeRenderer, 100);

        return new MarkdownConverter($environment);
    }

    /**
     * Create and configure the HTML Sanitizer allowlist.
     */
    protected function createSanitizer(): HtmlSanitizer
    {
        $tags = [
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'p', 'pre', 'code', 'span', 'div', 'blockquote', 'hr',
            'strong', 'b', 'em', 'i', 'u', 'del', 's', 'small', 'sub', 'sup',
            'ul', 'ol', 'li',
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td',
            'a', 'img', 'input',
        ];

        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeMedias()
            ->allowRelativeLinks()
            ->allowElement('input', ['type', 'checked', 'disabled', 'class'])
            ->allowElement('span', ['class', 'id'])
            ->allowElement('div', ['class', 'id', 'data-language'])
            ->allowAttribute('class', $tags)
            ->allowAttribute('id', $tags)
            ->allowAttribute('data-language', ['pre', 'code', 'div', 'span'])
            ->allowAttribute('width', ['img'])
            ->allowAttribute('height', ['img'])
            ->allowAttribute('alt', ['img'])
            ->allowAttribute('src', ['img'])
            ->allowAttribute('href', ['a'])
            ->allowAttribute('target', ['a'])
            ->allowAttribute('rel', ['a'])
            ->allowAttribute('title', $tags)
            ->allowAttribute('aria-hidden', $tags)
            ->allowAttribute('aria-label', $tags)
            ->withMaxInputLength(2_000_000);

        return new HtmlSanitizer($config);
    }
}
