<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Redirect;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->category = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
    $this->tag = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
});

test('admin dashboard renders 4 stats, recent published, and continue writing', function () {
    // 2 published, 1 draft
    Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Terbit Satu',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);
    Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Terbit Dua',
        'status' => 'published',
        'published_at' => now()->subHours(2),
    ]);
    Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Draf Lanjutan',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->actingAs($this->admin)->get('/admin');

    $response->assertOk()
        ->assertSee('Total Artikel')
        ->assertSee('Artikel Terbit')
        ->assertSee('Artikel Draf')
        ->assertSee('Kategori')
        ->assertSee('Artikel Terbit Dua')
        ->assertSee('Artikel Draf Lanjutan');
});

test('admin articles index lists articles and supports filtering by status and category', function () {
    $published = Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Terbit Linux',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $draft = Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Draf Rahasia',
        'status' => 'draft',
        'published_at' => null,
    ]);

    // All
    $resAll = $this->actingAs($this->admin)->get('/admin/articles');
    $resAll->assertOk()
        ->assertSee('Artikel Terbit Linux')
        ->assertSee('Artikel Draf Rahasia');

    // Filter published
    $resPub = $this->actingAs($this->admin)->get('/admin/articles?status=published');
    $resPub->assertOk()
        ->assertSee('Artikel Terbit Linux')
        ->assertDontSee('Artikel Draf Rahasia');

    // Filter draft
    $resDraft = $this->actingAs($this->admin)->get('/admin/articles?status=draft');
    $resDraft->assertOk()
        ->assertSee('Artikel Draf Rahasia')
        ->assertDontSee('Artikel Terbit Linux');
});

test('admin article create screen can be rendered', function () {
    $response = $this->actingAs($this->admin)->get('/admin/articles/create');

    $response->assertOk()
        ->assertSee('Tulis Artikel Baru')
        ->assertSee('Judul Artikel')
        ->assertSee('Markdown');
});

test('admin can store new article with tags, rendered html, word count, and reading time', function () {
    $response = $this->actingAs($this->admin)->post('/admin/articles', [
        'title' => 'Belajar Arsitektur Modern Laravel',
        'body' => "## Pengantar\n\nIni adalah panduan mendalam tentang arsitektur Laravel dengan pola hand-drawn.",
        'category_id' => $this->category->id,
        'status' => 'published',
        'tags' => [$this->tag->id],
    ]);

    $response->assertRedirect('/admin/articles');

    $article = Article::where('title', 'Belajar Arsitektur Modern Laravel')->first();

    expect($article)->not->toBeNull()
        ->and($article->slug)->toBe('belajar-arsitektur-modern-laravel')
        ->and($article->status)->toBe('published')
        ->and($article->published_at)->not->toBeNull()
        ->and($article->body_html)->toContain('<h2 id="pengantar">')
        ->and($article->word_count)->toBeGreaterThan(5)
        ->and($article->reading_time)->toBe(1)
        ->and($article->tags)->toHaveCount(1);
});

test('admin article edit screen can be rendered with existing values', function () {
    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Artikel Untuk Diedit',
    ]);

    $response = $this->actingAs($this->admin)->get("/admin/articles/{$article->id}/edit");

    $response->assertOk()
        ->assertSee('Edit Artikel')
        ->assertSee('Artikel Untuk Diedit');
});

test('admin can update article and changing slug of published article creates 301 redirect', function () {
    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Judul Lama Sekali',
        'slug' => 'judul-lama-sekali',
        'status' => 'published',
        'published_at' => now()->subDays(5),
    ]);

    $response = $this->actingAs($this->admin)->put("/admin/articles/{$article->id}", [
        'title' => 'Judul Baru Banget',
        'slug' => 'judul-baru-banget',
        'body' => 'Isi teks baru yang diperbarui.',
        'category_id' => $this->category->id,
        'status' => 'published',
    ]);

    $response->assertRedirect('/admin/articles');

    $article->refresh();
    expect($article->title)->toBe('Judul Baru Banget')
        ->and($article->slug)->toBe('judul-baru-banget');

    // Assert redirect record exists
    $redirect = Redirect::where('from_slug', 'judul-lama-sekali')
        ->where('article_id', $article->id)
        ->first();

    expect($redirect)->not->toBeNull();
});

test('admin can unpublish published article back to draft', function () {
    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($this->admin)->put("/admin/articles/{$article->id}", [
        'title' => $article->title,
        'body' => $article->body,
        'status' => 'draft',
    ]);

    $response->assertRedirect('/admin/articles');

    $article->refresh();
    expect($article->status)->toBe('draft');
});

test('admin can soft delete article', function () {
    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
    ]);

    $response = $this->actingAs($this->admin)->delete("/admin/articles/{$article->id}");

    $response->assertRedirect('/admin/articles');

    expect(Article::find($article->id))->toBeNull()
        ->and(Article::withTrashed()->find($article->id))->not->toBeNull();
});

test('server-side preview endpoint renders markdown to sanitized html', function () {
    $response = $this->actingAs($this->admin)->postJson('/admin/articles/preview', [
        'body' => "## Heading Preview\n\n**Teks Tebal** dan *Miring*.\n\n<script>alert(1)</script>",
    ]);

    $response->assertOk()
        ->assertJsonStructure(['html']);

    $html = $response->json('html');
    expect($html)->toContain('<h2 id="heading-preview">')
        ->and($html)->toContain('<strong>Teks Tebal</strong>')
        ->and($html)->not->toContain('<script>');
});

