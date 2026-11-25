<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('web app manifest returns valid manifest+json with dynamic settings and verified icon files', function () {
    Setting::set('blog_name', 'Dapit Notes');
    Setting::set('blog_description', 'Catatan teknologi dan pemrograman personal.');

    $response = $this->get('/manifest.webmanifest');

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('application/manifest+json');

    $data = $response->json();
    expect($data)->toBeArray()
        ->and($data['id'])->toBe('/')
        ->and($data['name'])->toBe('Dapit Notes')
        ->and($data['short_name'])->toBe('Dapit Notes')
        ->and($data['description'])->toBe('Catatan teknologi dan pemrograman personal.')
        ->and($data['start_url'])->toBe('/')
        ->and($data['scope'])->toBe('/')
        ->and($data['display'])->toBe('standalone')
        ->and($data['lang'])->toBe('id')
        ->and($data['icons'])->toHaveCount(4);

    foreach ($data['icons'] as $icon) {
        $path = public_path(ltrim($icon['src'], '/'));
        expect(file_exists($path))->toBeTrue();

        $dimensions = getimagesize($path);
        expect($dimensions)->not->toBeFalse();
        [$expectedW, $expectedH] = array_map('intval', explode('x', $icon['sizes']));
        expect($dimensions[0])->toBe($expectedW)
            ->and($dimensions[1])->toBe($expectedH);
    }

    expect(file_exists(public_path('icons/apple-touch-icon.png')))->toBeTrue()
        ->and(file_exists(public_path('icons/favicon-32x32.png')))->toBeTrue()
        ->and(file_exists(public_path('icons/favicon-16x16.png')))->toBeTrue()
        ->and(file_exists(public_path('favicon.ico')))->toBeFalse();
});

test('offline fallback page renders cleanly with noindex and home link', function () {
    $response = $this->get('/offline');

    $response->assertOk()
        ->assertSee('Sedang Offline', false)
        ->assertSee('noindex, nofollow', false)
        ->assertSee(route('home'), false);
});

test('service worker file exists and bypasses admin and auth routes', function () {
    $swPath = public_path('sw.js');
    expect(file_exists($swPath))->toBeTrue();

    $contents = file_get_contents($swPath);
    expect($contents)->toBeString()
        ->and($contents)->toContain("'/offline'")
        ->and($contents)->toContain("'/admin'")
        ->and($contents)->toContain("'/dashboard'")
        ->and($contents)->toContain("'/login'")
        ->and($contents)->toContain("'/profile'")
        ->and($contents)->toContain("request.method !== 'GET'");
});

test('layouts include PWA manifest, theme-color, apple-touch-icon, and respect custom favicon override', function () {
    $home = $this->get('/');
    $home->assertOk()
        ->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false)
        ->assertSee('<link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-touch-icon.png">', false)
        ->assertSee('id="pwa-theme-color"', false)
        ->assertSee('href="/icons/favicon-32x32.png"', false)
        ->assertSee('href="/icons/favicon-16x16.png"', false)
        ->assertDontSee('href="/favicon.ico"', false);

    $csp = $home->headers->get('Content-Security-Policy');
    expect($csp)->toContain("manifest-src 'self'")
        ->and($csp)->toContain("worker-src 'self'");

    Setting::set('favicon', 'https://example.com/custom-favicon.png');
    $customHome = $this->get('/');
    $customHome->assertOk()
        ->assertSee('<link rel="icon" href="https://example.com/custom-favicon.png">', false)
        ->assertDontSee('href="/icons/favicon-32x32.png"', false)
        ->assertDontSee('href="/favicon.ico"', false);

    $login = $this->get('/login');
    $login->assertOk()
        ->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);

    $admin = User::factory()->create();
    $dashboard = $this->actingAs($admin)->get('/dashboard');
    $dashboard->assertOk()
        ->assertSee('<link rel="manifest" href="/manifest.webmanifest">', false);
});

test('sitemap excludes offline and manifest endpoints', function () {
    $sitemap = $this->get('/sitemap.xml');

    $sitemap->assertOk()
        ->assertDontSee('/offline')
        ->assertDontSee('/manifest.webmanifest');
});
