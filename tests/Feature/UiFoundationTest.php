<?php

use App\Models\User;

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
