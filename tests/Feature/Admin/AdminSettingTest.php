<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create();
});

test('unauthenticated users cannot access admin settings', function () {
    $this->get('/admin/settings')->assertRedirect('/login');
    $this->put('/admin/settings')->assertRedirect('/login');
});

test('admin can view settings edit page', function () {
    Setting::set('blog_name', 'Blog Keren Dapit');
    Setting::set('blog_description', 'Deskripsi blog unik.');

    $response = $this->actingAs($this->admin)->get('/admin/settings');

    $response->assertOk()
        ->assertSee('Pengaturan Blog')
        ->assertSee('Blog Keren Dapit')
        ->assertSee('Deskripsi blog unik.');
});

test('admin can update blog identity, author, social links, about, and SEO default settings', function () {
    $response = $this->actingAs($this->admin)->put('/admin/settings', [
        'blog_name' => 'NPblog Super',
        'blog_tagline' => 'Tagline Baru Blog',
        'blog_description' => 'Deskripsi blog yang baru saja diperbarui.',
        'author_name' => 'Dapit Pratama',
        'author_bio' => 'Software developer dan minimalist designer.',
        'contact_email' => 'dapit@npblog.local',
        'social_links' => [
            'github' => 'https://github.com/dapit',
            'twitter' => 'https://x.com/dapit',
        ],
        'about_content' => "## Halo Semua\n\nIni adalah isi halaman About yang diperbarui.",
        'seo_default_title' => 'NPblog — Catatan Dapit',
        'seo_default_description' => 'Blog minimalis hand-drawn seputar arsitektur software.',
    ]);

    $response->assertRedirect('/admin/settings')
        ->assertSessionHas('success');

    expect(Setting::get('blog_name'))->toBe('NPblog Super')
        ->and(Setting::get('blog_tagline'))->toBe('Tagline Baru Blog')
        ->and(Setting::get('blog_description'))->toBe('Deskripsi blog yang baru saja diperbarui.')
        ->and(Setting::get('author_name'))->toBe('Dapit Pratama')
        ->and(Setting::get('author_bio'))->toBe('Software developer dan minimalist designer.')
        ->and(Setting::get('contact_email'))->toBe('dapit@npblog.local')
        ->and(Setting::get('social_links'))->toBe([
            'github' => 'https://github.com/dapit',
            'twitter' => 'https://x.com/dapit',
        ])
        ->and(Setting::get('about_content'))->toBe("## Halo Semua\n\nIni adalah isi halaman About yang diperbarui.")
        ->and(Setting::get('seo_default_title'))->toBe('NPblog — Catatan Dapit')
        ->and(Setting::get('seo_default_description'))->toBe('Blog minimalis hand-drawn seputar arsitektur software.');
});

test('admin can upload logo, hero image, and profile picture in settings', function () {
    $logoFile = UploadedFile::fake()->image('logo.png', 300, 100);
    $heroFile = UploadedFile::fake()->image('hero.jpg', 1600, 800);
    $avatarFile = UploadedFile::fake()->image('avatar.jpg', 400, 400);

    $response = $this->actingAs($this->admin)->put('/admin/settings', [
        'blog_name' => 'NPblog',
        'logo' => $logoFile,
        'hero_image' => $heroFile,
        'profile_image' => $avatarFile,
    ]);

    $response->assertRedirect('/admin/settings')
        ->assertSessionHas('success');

    $logoUrl = Setting::get('logo');
    $heroUrl = Setting::get('hero_image');
    $profileUrl = Setting::get('profile_image');

    expect($logoUrl)->not->toBeEmpty()
        ->and($heroUrl)->not->toBeEmpty()
        ->and($profileUrl)->not->toBeEmpty();
});

test('admin can remove existing uploaded images in settings', function () {
    Setting::set('logo', '/storage/media/old-logo.webp');
    Setting::set('profile_image', '/storage/media/old-avatar.webp');

    $response = $this->actingAs($this->admin)->put('/admin/settings', [
        'blog_name' => 'NPblog',
        'remove_logo' => '1',
        'remove_profile_image' => '1',
    ]);

    $response->assertRedirect('/admin/settings');

    expect(Setting::get('logo'))->toBe('')
        ->and(Setting::get('profile_image'))->toBe('');
});

test('settings validation rejects empty blog name or invalid email', function () {
    $response = $this->actingAs($this->admin)->put('/admin/settings', [
        'blog_name' => '',
        'contact_email' => 'bukan-email-valid',
    ]);

    $response->assertSessionHasErrors(['blog_name', 'contact_email']);
});

