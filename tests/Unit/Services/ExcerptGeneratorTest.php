<?php

use App\Services\ExcerptGenerator;

beforeEach(function () {
    $this->excerpt = new ExcerptGenerator;
});

test('uses explicit excerpt when provided', function () {
    $result = $this->excerpt->generate('Ini ringkasan manual.', 'Body artikel panjang...');

    expect($result)->toBe('Ini ringkasan manual.');
});

test('limits explicit excerpt to max length of 300 characters', function () {
    $longExcerpt = str_repeat('Ringkasan panjang sekali. ', 20);
    $result = $this->excerpt->generate($longExcerpt, 'Body...');

    expect(mb_strlen($result))->toBeLessThanOrEqual(300);
});

test('generates automatic fallback excerpt from first paragraph of body when null or empty', function () {
    $body = <<<'MD'
# Judul Artikel

Ini adalah paragraf pertama yang menjadi calon excerpt otomatis. Paragraf ini berisi informasi pembuka yang penting.

Ini adalah paragraf kedua yang seharusnya tidak dimasukkan ke dalam excerpt singkat.
MD;

    $result = $this->excerpt->generate(null, $body);

    expect($result)->toContain('Ini adalah paragraf pertama')
        ->and($result)->not->toContain('Ini adalah paragraf kedua')
        ->and($result)->not->toContain('# Judul Artikel');
});

test('strips markdown formatting and HTML tags from fallback excerpt', function () {
    $body = <<<'MD'
![Cover image](cover.jpg)

**Halo pembaca**, kunjungi [NPblog](https://example.com) untuk *catatan* dan <code>kode</code>!
MD;

    $result = $this->excerpt->generate('', $body);

    expect($result)->toBe('Halo pembaca, kunjungi NPblog untuk catatan dan kode!')
        ->and($result)->not->toContain('**')
        ->and($result)->not->toContain('![');
});

test('truncates very long first paragraph cleanly with ellipsis', function () {
    $longParagraph = str_repeat('Kalimat panjang yang diulang untuk menguji pemotongan excerpt. ', 10);
    $result = $this->excerpt->generate(null, $longParagraph);

    expect(mb_strlen($result))->toBeLessThanOrEqual(300)
        ->and($result)->toEndWith('...');
});

test('returns empty string when body is empty', function () {
    expect($this->excerpt->generate(null, ''))->toBe('')
        ->and($this->excerpt->generate('   ', '   '))->toBe('');
});
