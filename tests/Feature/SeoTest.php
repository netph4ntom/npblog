<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['name' => 'Dapit']);
    $this->category = Category::factory()->create(['name' => 'Architecture']);
    $this->tag = Tag::factory()->create(['name' => 'Laravel']);
});

test('article detail renders canonical, title, meta description, and open graph tags', function () {
    $media = Media::factory()->create([
        'user_id' => $this->admin->id,
        'path' => 'media/sample-og.webp',
    ]);

    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'featured_media_id' => $media->id,
        'title' => 'Panduan Lengkap Arsitektur Bersih',
        'slug' => 'panduan-lengkap-arsitektur-bersih',
        'excerpt' => 'Ringkasan panduan arsitektur bersih di Laravel.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    $article->tags()->attach($this->tag);

    $response = $this->get("/articles/{$article->slug}");

    $response->assertOk();

    // Canonical link
    $canonicalUrl = route('articles.show', $article->slug);
    $response->assertSee('<link rel="canonical" href="'.$canonicalUrl.'">', false);

    // Meta description
    $response->assertSee('<meta name="description" content="Ringkasan panduan arsitektur bersih di Laravel.">', false);

    // Open Graph
    $response->assertSee('<meta property="og:type" content="article">', false);
    $response->assertSee('<meta property="og:title" content="Panduan Lengkap Arsitektur Bersih">', false);
    $response->assertSee('<meta property="og:description" content="Ringkasan panduan arsitektur bersih di Laravel.">', false);
    $response->assertSee('<meta property="og:url" content="'.$canonicalUrl.'">', false);
    $response->assertSee('<meta property="og:image" content="'.$media->url.'">', false);

    // Article Open Graph extensions
    $response->assertSee('<meta property="article:published_time" content="'.$article->published_at->toIso8601String().'">', false);
    $response->assertSee('<meta property="article:section" content="Architecture">', false);
    $response->assertSee('<meta property="article:tag" content="Laravel">', false);

    // Twitter Card
    $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

test('article detail respects custom meta_title and meta_description', function () {
    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'title' => 'Judul Standar Artikel',
        'slug' => 'judul-standar-artikel',
        'excerpt' => 'Excerpt standar artikel.',
        'meta_title' => 'Judul Khusus SEO di Google',
        'meta_description' => 'Deskripsi khusus SEO yang dioptimasi untuk mesin pencari.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get("/articles/{$article->slug}");

    $response->assertOk();
    $response->assertSee('<title>Judul Khusus SEO di Google</title>', false);
    $response->assertSee('<meta name="description" content="Deskripsi khusus SEO yang dioptimasi untuk mesin pencari.">', false);
    $response->assertSee('<meta property="og:title" content="Judul Khusus SEO di Google">', false);
    $response->assertSee('<meta property="og:description" content="Deskripsi khusus SEO yang dioptimasi untuk mesin pencari.">', false);
});

test('article detail falls back to default OG image when article has no featured image', function () {
    Setting::set('hero_image', 'https://example.com/images/default-hero.webp');

    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'featured_media_id' => null,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get("/articles/{$article->slug}");

    $response->assertOk();
    $response->assertSee('<meta property="og:image" content="https://example.com/images/default-hero.webp">', false);
});

test('sitemap.xml returns published articles and excludes drafts or future scheduled posts', function () {
    $published = Article::factory()->create([
        'user_id' => $this->admin->id,
        'title' => 'Artikel Publik Tayang',
        'slug' => 'artikel-publik-tayang',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draft = Article::factory()->create([
        'user_id' => $this->admin->id,
        'title' => 'Artikel Rahasia Draf',
        'slug' => 'artikel-rahasia-draf',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $scheduled = Article::factory()->create([
        'user_id' => $this->admin->id,
        'title' => 'Artikel Masa Depan Terjadwal',
        'slug' => 'artikel-masa-depan-terjadwal',
        'status' => 'published',
        'published_at' => now()->addDays(5),
    ]);

    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('xml');

    // Contains home, articles, about
    $response->assertSee(route('home'), false);
    $response->assertSee(route('articles.index'), false);
    $response->assertSee(route('about'), false);

    // Contains published article
    $response->assertSee(route('articles.show', $published->slug), false);

    // MUST NOT contain draft or future scheduled
    $response->assertDontSee($draft->slug);
    $response->assertDontSee($scheduled->slug);
});

test('robots.txt disallows admin, login, profile, and references sitemap', function () {
    $response = $this->get('/robots.txt');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/plain');

    $response->assertSee('User-agent: *', false)
        ->assertSee('Disallow: /admin/', false)
        ->assertSee('Disallow: /login', false)
        ->assertDontSee('Disallow: /search', false)
        ->assertSee('Disallow: /profile', false)
        ->assertSee('Sitemap: '.route('sitemap'), false);
});

test('admin can manage article meta_title and meta_description in store and update', function () {
    $response = $this->actingAs($this->admin)->post('/admin/articles', [
        'title' => 'Artikel Baru dengan SEO',
        'body' => 'Konten artikel.',
        'status' => 'published',
        'meta_title' => 'Meta Title Awal',
        'meta_description' => 'Meta Description Awal',
    ]);

    $response->assertRedirect('/admin/articles');

    $article = Article::where('title', 'Artikel Baru dengan SEO')->firstOrFail();
    expect($article->meta_title)->toBe('Meta Title Awal')
        ->and($article->meta_description)->toBe('Meta Description Awal');

    // Update
    $responseUpdate = $this->actingAs($this->admin)->put("/admin/articles/{$article->id}", [
        'title' => 'Artikel Baru dengan SEO',
        'body' => 'Konten artikel.',
        'status' => 'published',
        'meta_title' => 'Meta Title Baru',
        'meta_description' => 'Meta Description Baru',
    ]);

    $responseUpdate->assertRedirect('/admin/articles');
    $article->refresh();
    expect($article->meta_title)->toBe('Meta Title Baru')
        ->and($article->meta_description)->toBe('Meta Description Baru');
});
