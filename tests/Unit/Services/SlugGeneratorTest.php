<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Redirect;
use App\Models\User;
use App\Services\SlugGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->generator = new SlugGenerator;
    $this->user = User::factory()->create();
    $this->category = Category::factory()->create();
});

test('generates basic lowercase slug with dashes', function () {
    $slug = $this->generator->generate('Belajar Laravel 12 & Modern PHP');

    expect($slug)->toBe('belajar-laravel-12-modern-php');
});

test('collapses duplicate separators and trims whitespace', function () {
    $slug = $this->generator->generate('   Halo --- Dunia  !!!   ');

    expect($slug)->toBe('halo-dunia');
});

test('handles collision by appending numeric suffix', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'slug' => 'panduan-docker',
    ]);

    $newSlug = $this->generator->generate('Panduan Docker');

    expect($newSlug)->toBe('panduan-docker-2');
});

test('handles consecutive collisions incrementing suffix', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'slug' => 'panduan-docker',
    ]);
    Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'slug' => 'panduan-docker-2',
    ]);

    $newSlug = $this->generator->generate('Panduan Docker');

    expect($newSlug)->toBe('panduan-docker-3');
});

test('respects ignoreId when updating existing model', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'title' => 'Panduan Docker',
        'slug' => 'panduan-docker',
    ]);

    $sameSlug = $this->generator->generate('Panduan Docker', ignoreId: $article->id);

    expect($sameSlug)->toBe('panduan-docker');
});

test('checks soft-deleted records to prevent database unique collision', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'slug' => 'panduan-docker',
    ]);
    $article->delete(); // Soft deleted

    $newSlug = $this->generator->generate('Panduan Docker');

    expect($newSlug)->toBe('panduan-docker-2');
});

test('avoids forbidden route slugs', function () {
    $forbiddenWords = ['admin', 'login', 'register', 'dashboard', 'about', 'search', 'articles', 'api', 'feed', 'sitemap'];

    foreach ($forbiddenWords as $word) {
        $slug = $this->generator->generate($word);
        expect($slug)->not->toBe($word)
            ->and($slug)->toStartWith($word.'-');
    }
});

test('provides fallback slug if title yields empty string', function () {
    expect($this->generator->generate('??? !!! ...'))->toBe('post')
        ->and($this->generator->generate(''))->toBe('post')
        ->and($this->generator->generate('   ---   '))->toBe('post');
});

test('works for different models like Category', function () {
    Category::factory()->create(['slug' => 'teknologi']);

    $newSlug = $this->generator->generate('Teknologi', modelClass: Category::class);

    expect($newSlug)->toBe('teknologi-2');
});

test('checks redirects table to prevent collision with redirected slugs', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->category->id,
        'slug' => 'panduan-docker-baru',
    ]);

    Redirect::create([
        'from_slug' => 'panduan-docker',
        'article_id' => $article->id,
    ]);

    $newSlug = $this->generator->generate('Panduan Docker');

    expect($newSlug)->toBe('panduan-docker-2');
});
