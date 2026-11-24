<?php

use App\Models\User;
use Carbon\Carbon;

test('public layout renders home with theme toggle and skip link', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('NPblog');
    $response->assertSee('Loncat ke konten utama');
    $response->assertSee('Toggle tema gelap atau terang');
    $response->assertSee('Home');
    $response->assertSee('Articles');
    $response->assertSee('About');
});

test('admin layout renders for authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('NPblog Admin');
    $response->assertSee('Articles');
    $response->assertSee('Categories');
    $response->assertSee('Media');
    $response->assertSee('Settings');
    $response->assertSee('Profile');
});

test('self hosted font files exist in public directory', function () {
    expect(file_exists(public_path('fonts/caveat-700.woff2')))->toBeTrue();
    expect(file_exists(public_path('fonts/patrick-hand-400.woff2')))->toBeTrue();
    expect(file_exists(public_path('fonts/dm-sans-400.woff2')))->toBeTrue();
    expect(file_exists(public_path('fonts/dm-sans-700.woff2')))->toBeTrue();
    expect(file_exists(public_path('fonts/jetbrains-mono-400.woff2')))->toBeTrue();
});

test('sketch components render with proper hand drawn attributes', function () {
    $view = $this->blade('<x-sketch-box variant="a">Test Box</x-sketch-box>');
    expect((string) $view)->toContain('sketch-border', 'sketch-radius-a', 'sketch-shadow');

    $viewB = $this->blade('<x-sketch-box variant="b">Test Box B</x-sketch-box>');
    expect((string) $viewB)->toContain('sketch-radius-b');

    $viewBtn = $this->blade('<x-button variant="primary">Submit</x-button>');
    expect((string) $viewBtn)->toContain('font-ui', 'sketch-border', 'sketch-shadow');

    $viewChip = $this->blade('<x-chip active="true">Tech</x-chip>');
    expect((string) $viewChip)->toContain('font-ui', 'rounded-full');

    $viewBadge = $this->blade('<x-badge type="draft">Draft</x-badge>');
    expect((string) $viewBadge)->toContain('bg-warning-bg', 'text-warning-text');
});

test('application timezone is Asia/Jakarta and default locale is id', function () {
    expect(config('app.timezone'))->toBe('Asia/Jakarta')
        ->and(config('app.locale'))->toBe('id');

    $date = Carbon::parse('2026-08-17 10:00:00');
    expect($date->translatedFormat('d F Y'))->toBe('17 Agustus 2026');
});

test('public header is transparent at top and dynamically adapts on scroll', function () {
    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('isScrolled', false)
        ->assertSee('bg-transparent', false)
        ->assertSee('bg-bg/95', false);
});

test('button component automatically renders as anchor when href is provided', function () {
    $view = $this->blade('<x-button href="/articles" variant="primary">Mulai Membaca</x-button>');
    expect((string) $view)->toContain('<a ', 'href="/articles"')
        ->and((string) $view)->not->toContain('<button');
});

test('admin layout sidebar is fixed and supports collapsing to icons', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('sidebarCollapsed', false)
        ->assertSee('toggleSidebar', false)
        ->assertSee('sticky top-0 h-screen', false)
        ->assertSee('w-20', false);
});
