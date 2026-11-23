<?php

declare(strict_types=1);

namespace App\Services\Markdown;

use Highlight\Highlighter;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\CommonMark\Node\Block\IndentedCode;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

class HighlightedCodeRenderer implements NodeRendererInterface
{
    protected Highlighter $highlighter;

    public function __construct(?Highlighter $highlighter = null)
    {
        $this->highlighter = $highlighter ?? new Highlighter;
    }

    public function render(Node $node, ChildNodeRendererInterface $childRenderer): string
    {
        if (! $node instanceof FencedCode && ! $node instanceof IndentedCode) {
            throw new \InvalidArgumentException('Incompatible node type: '.$node::class);
        }

        $info = $node instanceof FencedCode ? trim($node->getInfo() ?? '') : '';
        $language = $info !== '' ? explode(' ', $info)[0] : '';
        $code = $node->getLiteral();

        $highlighted = '';
        if ($language !== '') {
            try {
                $highlightResult = $this->highlighter->highlight($language, $code);
                $highlighted = $highlightResult->value;
            } catch (\Throwable) {
                // Fallback for unsupported or custom language
                $highlighted = htmlspecialchars($code, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
            }
        } else {
            $highlighted = htmlspecialchars($code, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $escapedLang = htmlspecialchars($language, ENT_QUOTES, 'UTF-8');
        $langAttr = $language !== '' ? " data-language=\"{$escapedLang}\"" : '';
        $langClass = $language !== '' ? " language-{$escapedLang}" : '';

        return "<pre class=\"code-block hljs{$langClass}\"{$langAttr}><code class=\"hljs{$langClass}\">{$highlighted}</code></pre>";
    }
}
