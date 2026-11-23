<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create();
    $this->category = Category::factory()->create(['name' => 'Tech']);
});

test('skip link exists on public and admin pages pointing to #main-content', function () {
    // Public home
    $resPublic = $this->get('/');
    $resPublic->assertOk();
    $resPublic->assertSee('href="#main-content"', false);
    $resPublic->assertSee('Loncat ke konten utama');

    // Admin dashboard
    $resAdmin = $this->actingAs($this->admin)->get('/admin');
    $resAdmin->assertOk();
    $resAdmin->assertSee('href="#main-content"', false);
    $resAdmin->assertSee('Loncat ke konten utama');
});

test('pages provide main landmark with id main-content', function () {
    $response = $this->get('/');
    $response->assertOk();
    $response->assertSee('<main id="main-content"', false);

    $adminResponse = $this->actingAs($this->admin)->get('/admin');
    $adminResponse->assertOk();
    $adminResponse->assertSee('<main id="main-content"', false);
});

test('article detail page has proper heading structure and semantic landmarks', function () {
    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'category_id' => $this->category->id,
        'title' => 'Struktur Semantik Artikel',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get("/articles/{$article->slug}");

    $response->assertOk();
    // H1 exists
    $response->assertSee('<h1', false);
    $response->assertSee('Struktur Semantik Artikel');
    // Article landmark exists
    $response->assertSee('<article', false);
    // Header landmark exists
    $response->assertSee('<header', false);
});

test('interactive buttons and controls have minimum touch targets', function () {
    $response = $this->get('/');
    $response->assertOk();

    // Check for min-h-[44px] presence in response
    $response->assertSee('min-h-[44px]', false);
});

test('icon buttons and toggles provide accessible aria-labels', function () {
    $response = $this->get('/');
    $response->assertOk();

    // Theme toggle aria-label
    $response->assertSee('aria-label="Toggle tema gelap atau terang"', false);

    // Mobile menu toggle aria-label
    $response->assertSee('aria-label="Buka menu navigasi"', false);
});

test('confirm dialog modal has role dialog and aria-modal attributes', function () {
    $adminResponse = $this->actingAs($this->admin)->get('/admin/articles');
    $adminResponse->assertOk();

    $adminResponse->assertSee('role="dialog"', false);
    $adminResponse->assertSee('aria-modal="true"', false);
    $adminResponse->assertSee('@keydown.escape.window', false);
});

test('css stylesheet defines prefers-reduced-motion and prefers-contrast rules', function () {
    $cssContent = file_get_contents(resource_path('css/app.css'));

    expect($cssContent)->toContain('@media (prefers-reduced-motion: reduce)')
        ->and($cssContent)->toContain('@media (prefers-contrast: more)')
        ->and($cssContent)->toContain(':focus-visible')
        ->and($cssContent)->toContain('--ink')
        ->and($cssContent)->toContain('--bg');
});
