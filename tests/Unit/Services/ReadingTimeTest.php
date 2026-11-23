<?php

use App\Services\ReadingTime;

beforeEach(function () {
    $this->readingTime = new ReadingTime;
});

test('counts words in plain text accurately', function () {
    $text = 'NPblog adalah blog personal minimalis dengan gaya hand-drawn.';
    $count = $this->readingTime->countWords($text);

    expect($count)->toBe(9);
});

test('strips markdown syntax before counting words', function () {
    $markdown = <<<'MD'
# Judul Utama

Ini adalah sebuah **paragraf** dengan [tautan](https://example.com) dan gambar ![gambar](image.jpg).

```php
echo "Kode ini dihitung atau diabaikan?";
```

- Poin 1
- Poin 2
MD;

    $count = $this->readingTime->countWords($markdown);

    // Markdown symbols (#, **, [], (), !, ```) are stripped, words inside are counted cleanly
    expect($count)->toBeGreaterThan(10)
        ->and($count)->toBeLessThan(25);
});

test('returns minimum 1 minute reading time for empty or short content', function () {
    expect($this->readingTime->estimateMinutes(''))->toBe(1)
        ->and($this->readingTime->estimateMinutes('Satu dua tiga'))->toBe(1)
        ->and($this->readingTime->estimateMinutes(str_repeat('kata ', 199)))->toBe(1)
        ->and($this->readingTime->estimateMinutes(str_repeat('kata ', 200)))->toBe(1);
});

test('calculates reading time using ceil(words / 200)', function () {
    // 201 words should round up to 2 minutes
    $words201 = str_repeat('kata ', 201);
    expect($this->readingTime->estimateMinutes($words201))->toBe(2);

    // 400 words is 2 minutes
    $words400 = str_repeat('kata ', 400);
    expect($this->readingTime->estimateMinutes($words400))->toBe(2);

    // 401 words is 3 minutes
    $words401 = str_repeat('kata ', 401);
    expect($this->readingTime->estimateMinutes($words401))->toBe(3);
});

test('analyze returns array with word_count and reading_time', function () {
    $markdown = str_repeat('kata ', 250);
    $result = $this->readingTime->analyze($markdown);

    expect($result)->toBeArray()
        ->and($result['word_count'])->toBe(250)
        ->and($result['reading_time'])->toBe(2);
});
