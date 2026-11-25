<?php

use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create();
});

test('unauthenticated users cannot access admin media routes', function () {
    $this->get('/admin/media')->assertRedirect('/login');
    $this->post('/admin/media')->assertRedirect('/login');
});

test('admin can view media library index', function () {
    Media::factory()->create([
        'user_id' => $this->admin->id,
        'original_name' => 'gambar-pertama.webp',
        'alt_text' => 'Deskripsi pertama',
    ]);

    $response = $this->actingAs($this->admin)->get('/admin/media');

    $response->assertOk()
        ->assertSee('Galeri Media')
        ->assertSee('Unggah Gambar Baru')
        ->assertSee('gambar-pertama.webp')
        ->assertSee('Deskripsi pertama')
        ->assertDontSee('Sanitasi & EXIF', false)
        ->assertDontSee('Resize 1600px', false)
        ->assertDontSee('WebP + Thumbnails', false);
});

test('admin can upload new image via media manager', function () {
    $file = UploadedFile::fake()->image('artikel-header.jpg', 1200, 800);

    $response = $this->actingAs($this->admin)->post('/admin/media', [
        'image' => $file,
        'alt_text' => 'Foto header artikel',
    ]);

    $response->assertRedirect('/admin/media')
        ->assertSessionHas('success');

    $media = Media::where('alt_text', 'Foto header artikel')->first();
    expect($media)->not->toBeNull()
        ->and($media->original_name)->toBe('artikel-header.jpg')
        ->and($media->mime)->toBe('image/webp');

    Storage::disk('public')->assertExists($media->path);
});

test('admin can update alt text of existing media', function () {
    $media = Media::factory()->create([
        'user_id' => $this->admin->id,
        'alt_text' => 'Teks lama',
    ]);

    $response = $this->actingAs($this->admin)->put("/admin/media/{$media->id}", [
        'alt_text' => 'Teks baru yang diperbarui',
    ]);

    $response->assertRedirect('/admin/media')
        ->assertSessionHas('success');

    expect($media->fresh()->alt_text)->toBe('Teks baru yang diperbarui');
});

test('admin can delete media item and storage files are deleted', function () {
    $file = UploadedFile::fake()->image('hapus-saya.png', 400, 300);
    $this->actingAs($this->admin)->post('/admin/media', [
        'image' => $file,
        'alt_text' => 'Akan dihapus',
    ]);

    $media = Media::where('alt_text', 'Akan dihapus')->firstOrFail();
    $path = $media->path;
    Storage::disk('public')->assertExists($path);

    $response = $this->actingAs($this->admin)->delete("/admin/media/{$media->id}");
    $response->assertRedirect('/admin/media')
        ->assertSessionHas('success');

    expect(Media::find($media->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('media upload rejects dangerous files with validation error', function () {
    $svg = UploadedFile::fake()->createWithContent('test.svg', '<svg></svg>');

    $response = $this->actingAs($this->admin)->post('/admin/media', [
        'image' => $svg,
    ]);

    $response->assertSessionHasErrors(['image']);
});

test('admin cannot delete media in use by articles as featured image', function () {
    $media = Media::factory()->create([
        'user_id' => $this->admin->id,
        'alt_text' => 'Featured Media Terpakai',
    ]);

    Article::factory()->create([
        'user_id' => $this->admin->id,
        'featured_media_id' => $media->id,
    ]);

    $response = $this->actingAs($this->admin)->delete("/admin/media/{$media->id}");

    $response->assertRedirect()
        ->assertSessionHas('error');

    expect(Media::find($media->id))->not->toBeNull();
});
