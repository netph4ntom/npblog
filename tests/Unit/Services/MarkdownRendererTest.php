<?php

use App\Services\MarkdownRenderer;

beforeEach(function () {
    $this->renderer = new MarkdownRenderer;
});

test('renders basic markdown syntax to HTML', function () {
    $markdown = '**Teks tebal** dan *teks miring* serta `inline code`.';
    $html = $this->renderer->render($markdown);

    expect($html)->toContain('<strong>Teks tebal</strong>')
        ->and($html)->toContain('<em>teks miring</em>')
        ->and($html)->toContain('<code>inline code</code>');
});

test('renders GFM tables', function () {
    $markdown = <<<'MD'
| Header 1 | Header 2 |
| --- | --- |
| Nilai A | Nilai B |
MD;

    $html = $this->renderer->render($markdown);

    expect($html)->toContain('<table')
        ->and($html)->toContain('<th>Header 1</th>')
        ->and($html)->toContain('<td>Nilai A</td>');
});

test('renders GFM task lists and strikethrough', function () {
    $markdown = <<<'MD'
- [x] Tugas selesai
- [ ] Tugas belum
~~coret teks~~
MD;

    $html = $this->renderer->render($markdown);

    expect($html)->toContain('<del>coret teks</del>')
        ->and($html)->toContain('type="checkbox"');
});

test('generates anchor headings with id attribute and permalink', function () {
    $markdown = '## Panduan Arsitektur NPblog';
    $html = $this->renderer->render($markdown);

    expect($html)->toMatch('/<h2[^>]*id="panduan-arsitektur-npblog"[^>]*>/')
        ->and($html)->toContain('href="#panduan-arsitektur-npblog"');
});

test('sanitizes dangerous HTML tags and XSS vectors', function () {
    $malicious = <<<'MD'
<script>alert('XSS');</script>

<iframe src="https://evil.com"></iframe>

<object data="evil.swf"></object>

<img src="x" onerror="alert('hack')">

[Klik saya](javascript:alert(1))
MD;

    $html = $this->renderer->render($malicious);

    expect($html)->not->toContain('<script>')
        ->and($html)->not->toContain('<iframe')
        ->and($html)->not->toContain('<object')
        ->and($html)->not->toContain('onerror')
        ->and($html)->not->toContain('javascript:');
});

test('allows safe HTML elements and attributes', function () {
    $safe = <<<'MD'
<p class="intro">Paragraf aman dengan <a href="https://example.com" title="Test">link</a> dan <img src="/images/safe.webp" alt="Deskripsi gambar" width="800" height="400">.</p>
MD;

    $html = $this->renderer->render($safe);

    expect($html)->toContain('<p class="intro">')
        ->and($html)->toContain('<a href="https://example.com"')
        ->and($html)->toContain('alt="Deskripsi gambar"');
});

test('highlights code blocks with hljs classes and language metadata', function () {
    $markdown = <<<'MD'
```php
function sapa(string $nama): string {
    return "Halo, " . $nama;
}
```
MD;

    $html = $this->renderer->render($markdown);

    expect($html)->toContain('<pre')
        ->and($html)->toContain('<code')
        ->and($html)->toContain('hljs')
        ->and($html)->toContain('function')
        ->and($html)->toContain('data-language="php"');
});

test('handles unknown language gracefully without crashing', function () {
    $markdown = <<<'MD'
```unknownlang123
some special custom code syntax
```
MD;

    $html = $this->renderer->render($markdown);

    expect($html)->toContain('some special custom code syntax')
        ->and($html)->toContain('<pre')
        ->and($html)->toContain('<code');
});
