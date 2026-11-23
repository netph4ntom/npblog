<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

test('category can be created and has articles relationship', function () {
    $category = Category::factory()->create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    $article = Article::factory()->create([
        'category_id' => $category->id,
    ]);

    expect($category->articles)->toHaveCount(1)
        ->and($category->articles->first()->id)->toBe($article->id);
});

test('tag can be created and linked to articles via pivot', function () {
    $tag = Tag::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    $article = Article::factory()->create();
    $article->tags()->attach($tag);

    expect($article->tags)->toHaveCount(1)
        ->and($tag->articles)->toHaveCount(1)
        ->and($tag->articles->first()->id)->toBe($article->id);
});

test('media can be created and linked to user and article', function () {
    $user = User::factory()->create();
    $media = Media::factory()->create([
        'user_id' => $user->id,
        'path' => 'media/sample.webp',
        'disk' => 'public',
    ]);

    $article = Article::factory()->create([
        'user_id' => $user->id,
        'featured_media_id' => $media->id,
    ]);

    expect($media->user->id)->toBe($user->id)
        ->and($article->featuredMedia->id)->toBe($media->id)
        ->and($media->articles)->toHaveCount(1)
        ->and($media->url)->toContain('sample.webp');
});

test('article scope published only includes published articles past or present', function () {
    // 1. Published in the past (must be included)
    $published = Article::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    // 2. Draft (must be excluded)
    $draft = Article::factory()->draft()->create();

    // 3. Scheduled in the future (must be excluded)
    $scheduled = Article::factory()->scheduled()->create();

    // 4. Published with null date (must be excluded)
    $nullDate = Article::factory()->create([
        'status' => 'published',
        'published_at' => null,
    ]);

    $results = Article::published()->get();

    expect($results->pluck('id'))->toContain($published->id)
        ->and($results->pluck('id'))->not->toContain($draft->id)
        ->and($results->pluck('id'))->not->toContain($scheduled->id)
        ->and($results->pluck('id'))->not->toContain($nullDate->id);

    expect($published->isPublished())->toBeTrue()
        ->and($draft->isPublished())->toBeFalse()
        ->and($scheduled->isPublished())->toBeFalse();
});

test('article scope featured only includes published featured articles', function () {
    $featuredPublished = Article::factory()->featured()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $featuredDraft = Article::factory()->featured()->draft()->create();

    $nonFeatured = Article::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'is_featured' => false,
    ]);

    $results = Article::featured()->get();

    expect($results->pluck('id'))->toContain($featuredPublished->id)
        ->and($results->pluck('id'))->not->toContain($featuredDraft->id)
        ->and($results->pluck('id'))->not->toContain($nonFeatured->id);
});

test('article supports soft deletes', function () {
    $article = Article::factory()->create();
    $articleId = $article->id;

    $article->delete();

    expect(Article::query()->find($articleId))->toBeNull()
        ->and(Article::withTrashed()->find($articleId))->not->toBeNull();
});

test('redirect points to target article', function () {
    $article = Article::factory()->create([
        'slug' => 'current-slug',
    ]);

    $redirect = Redirect::factory()->create([
        'article_id' => $article->id,
        'from_slug' => 'old-slug',
    ]);

    expect($redirect->article->id)->toBe($article->id)
        ->and($article->redirects)->toHaveCount(1);
});

test('setting helper can get and set strings and json arrays', function () {
    Setting::set('test_string', 'hello world');
    expect(Setting::get('test_string'))->toBe('hello world');

    Setting::set('test_array', ['a' => 1, 'b' => 2]);
    expect(Setting::get('test_array'))->toBe(['a' => 1, 'b' => 2]);

    expect(Setting::get('non_existent', 'default_val'))->toBe('default_val');
});

test('database seeder seeds 1 admin, 4 categories, and 12 articles', function () {
    $this->seed(DatabaseSeeder::class);

    expect(User::where('email', 'admin@npblog.local')->exists())->toBeTrue()
        ->and(Category::count())->toBe(4)
        ->and(Article::count())->toBe(12)
        ->and(Article::published()->count())->toBe(10)
        ->and(Article::featured()->count())->toBe(2);
});
