<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create();
    $this->category = Category::factory()->create();
});

test('admin can create article with featured image and alt text', function () {
    $file = UploadedFile::fake()->image('featured-banner.jpg', 1600, 900);

    $response = $this->actingAs($this->admin)->post('/admin/articles', [
        'title' => 'Artikel Bergambar Utama',
        'body' => 'Konten artikel dengan featured image.',
        'category_id' => $this->category->id,
        'status' => 'published',
        'featured_image' => $file,
        'featured_alt_text' => 'Banner artikel utama',
    ]);

    $response->assertRedirect('/admin/articles');

    $article = Article::where('title', 'Artikel Bergambar Utama')->first();
    expect($article)->not->toBeNull()
        ->and($article->featured_media_id)->not->toBeNull();

    $media = $article->featuredMedia;
    expect($media)->not->toBeNull()
        ->and($media->alt_text)->toBe('Banner artikel utama')
        ->and($media->mime)->toBe('image/webp');

    Storage::disk('public')->assertExists($media->path);
});

test('admin updating article and replacing featured image cleans up old file and thumbnails', function () {
    // 1. Create article with first image
    $firstFile = UploadedFile::fake()->image('pertama.jpg', 800, 600);
    $this->actingAs($this->admin)->post('/admin/articles', [
        'title' => 'Artikel Ganti Gambar',
        'body' => 'Isi artikel awal.',
        'category_id' => $this->category->id,
        'status' => 'published',
        'featured_image' => $firstFile,
        'featured_alt_text' => 'Gambar Lama',
    ]);

    $article = Article::where('title', 'Artikel Ganti Gambar')->firstOrFail();
    $oldMedia = $article->featuredMedia;
    $oldPath = $oldMedia->path;
    $oldHash = pathinfo($oldPath, PATHINFO_FILENAME);

    Storage::disk('public')->assertExists($oldPath);
    Storage::disk('public')->assertExists("media/thumbnails/{$oldHash}-400.webp");

    // 2. Replace with new image
    $newFile = UploadedFile::fake()->image('kedua.jpg', 1200, 800);
    $response = $this->actingAs($this->admin)->put("/admin/articles/{$article->id}", [
        'title' => 'Artikel Ganti Gambar',
        'body' => 'Isi artikel diperbarui.',
        'status' => 'published',
        'featured_image' => $newFile,
        'featured_alt_text' => 'Gambar Baru',
    ]);

    $response->assertRedirect('/admin/articles');

    $article->refresh();
    $newMedia = $article->featuredMedia;

    expect($newMedia->id)->not->toBe($oldMedia->id)
        ->and($newMedia->alt_text)->toBe('Gambar Baru');

    // New file exists
    Storage::disk('public')->assertExists($newMedia->path);

    // Old file and its thumbnails MUST be cleaned up from disk and DB
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertMissing("media/thumbnails/{$oldHash}-400.webp");
    expect(Media::find($oldMedia->id))->toBeNull();
});

test('admin can remove featured image from article and old file is cleaned up', function () {
    $file = UploadedFile::fake()->image('gambar-dihapus.jpg', 600, 400);
    $this->actingAs($this->admin)->post('/admin/articles', [
        'title' => 'Artikel Hapus Gambar',
        'body' => 'Isi artikel.',
        'status' => 'draft',
        'featured_image' => $file,
    ]);

    $article = Article::where('title', 'Artikel Hapus Gambar')->firstOrFail();
    $oldMedia = $article->featuredMedia;
    $oldPath = $oldMedia->path;

    Storage::disk('public')->assertExists($oldPath);

    // Remove image
    $response = $this->actingAs($this->admin)->put("/admin/articles/{$article->id}", [
        'title' => 'Artikel Hapus Gambar',
        'body' => 'Isi artikel.',
        'status' => 'draft',
        'remove_featured_image' => '1',
    ]);

    $response->assertRedirect('/admin/articles');

    $article->refresh();
    expect($article->featured_media_id)->toBeNull();
    Storage::disk('public')->assertMissing($oldPath);
    expect(Media::find($oldMedia->id))->toBeNull();
});

test('public article detail page renders featured image with width, height, and alt text', function () {
    $file = UploadedFile::fake()->image('publik-featured.jpg', 1200, 800);
    $this->actingAs($this->admin)->post('/admin/articles', [
        'title' => 'Artikel Publik Bergambar',
        'body' => 'Konten publik yang luar biasa.',
        'status' => 'published',
        'featured_image' => $file,
        'featured_alt_text' => 'Alt Teks Khusus Publik',
    ]);

    $article = Article::where('title', 'Artikel Publik Bergambar')->firstOrFail();

    $response = $this->get("/articles/{$article->slug}");

    $response->assertOk()
        ->assertSee('Alt Teks Khusus Publik')
        ->assertSee((string) $article->featuredMedia->width)
        ->assertSee((string) $article->featuredMedia->height);
});
