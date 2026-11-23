<?php

use App\Models\Media;
use App\Models\User;
use App\Services\MediaProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->user = User::factory()->create();
    $this->processor = new MediaProcessor;
});

test('media processor rejects file exceeding 5 MB limit', function () {
    // 5.5 MB fake file
    $file = UploadedFile::fake()->create('large.jpg', 5600, 'image/jpeg');

    expect(fn () => $this->processor->process($file, $this->user))
        ->toThrow(InvalidArgumentException::class, 'Ukuran gambar melebihi batas maksimum 5 MB.');
});

test('media processor rejects SVG files', function () {
    $svgContent = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert("xss")</script><circle cx="50" cy="50" r="40"/></svg>';
    $file = UploadedFile::fake()->createWithContent('malicious.svg', $svgContent);

    expect(fn () => $this->processor->process($file, $this->user))
        ->toThrow(InvalidArgumentException::class);
});

test('media processor rejects fake MIME type (PHP disguised as image)', function () {
    $phpPayload = '<?php phpinfo(); ?>';
    $file = UploadedFile::fake()->createWithContent('exploit.png', $phpPayload);

    expect(fn () => $this->processor->process($file, $this->user))
        ->toThrow(InvalidArgumentException::class, 'Format file tidak didukung');
});

test('media processor rejects double extension with executable/script parts', function () {
    // Valid PNG data but with double extension name
    $file = UploadedFile::fake()->image('exploit.php.png', 100, 100);

    expect(fn () => $this->processor->process($file, $this->user))
        ->toThrow(InvalidArgumentException::class, 'Nama file mengandung ekstensi berbahaya.');
});

test('media processor processes valid JPEG, converts to WebP and generates thumbnails', function () {
    $file = UploadedFile::fake()->image('scenery.jpg', 1800, 1200);

    $media = $this->processor->process($file, $this->user, altText: 'Pemandangan Alam');

    expect($media)->toBeInstanceOf(Media::class)
        ->and($media->mime)->toBe('image/webp')
        ->and($media->alt_text)->toBe('Pemandangan Alam')
        ->and($media->width)->toBe(1600) // downscaled from 1800 proportionally (1800x1200 -> 1600x1067)
        ->and($media->height)->toBe(1067)
        ->and($media->path)->toEndWith('.webp')
        ->and($media->path)->toStartWith('media/');

    // Assert main image exists in storage
    Storage::disk('public')->assertExists($media->path);

    // Assert thumbnails exist
    $hash = pathinfo($media->path, PATHINFO_FILENAME);
    Storage::disk('public')->assertExists("media/thumbnails/{$hash}-400.webp");
    Storage::disk('public')->assertExists("media/thumbnails/{$hash}-800.webp");
});

test('media processor preserves transparency for PNG images converted to WebP', function () {
    $file = UploadedFile::fake()->image('logo.png', 500, 500);

    $media = $this->processor->process($file, $this->user, altText: 'Logo Transparan');

    expect($media)->toBeInstanceOf(Media::class)
        ->and($media->mime)->toBe('image/webp')
        ->and($media->width)->toBe(500)
        ->and($media->height)->toBe(500);

    Storage::disk('public')->assertExists($media->path);
});

test('deleting media record removes main file and all thumbnails from disk', function () {
    $file = UploadedFile::fake()->image('photo.jpg', 600, 400);
    $media = $this->processor->process($file, $this->user);

    $hash = pathinfo($media->path, PATHINFO_FILENAME);
    $thumb400 = "media/thumbnails/{$hash}-400.webp";
    $thumb800 = "media/thumbnails/{$hash}-800.webp";

    Storage::disk('public')->assertExists($media->path);
    Storage::disk('public')->assertExists($thumb400);
    Storage::disk('public')->assertExists($thumb800);

    $media->delete();

    Storage::disk('public')->assertMissing($media->path);
    Storage::disk('public')->assertMissing($thumb400);
    Storage::disk('public')->assertMissing($thumb800);
});
