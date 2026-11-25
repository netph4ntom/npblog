<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
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

test('admin user bottom menu contains profile setting and logout options', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('userMenuOpen', false)
        ->assertSee('Profile Setting', false)
        ->assertSee(route('logout'), false);

    expect(substr_count($response->getContent(), 'userMenuOpen: false'))->toBe(2);
});

test('pagination uses theme tokens and hand-drawn styling without hardcoded gray utilities', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    Article::factory()->count(2)->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $paginator = Article::paginate(1);

    foreach (['pagination::tailwind', 'pagination::default'] as $view) {
        $html = (string) $paginator->links($view);

        expect($html)->toContain('border-ink', 'bg-surface', 'bg-accent-dark', 'text-on-accent')
            ->and($html)->not->toContain('dark:bg-gray-800', 'dark:bg-gray-700', 'dark:border-gray-600')
            ->and($html)->not->toContain('shadow-xs')
            ->and($html)->toContain('min-h-[44px]', '1 / 2', 'whitespace-nowrap')
            ->and($html)->toMatch('/class="[^"]*sm:hidden[^"]*".*<!--\s*Mobile Navigation Buttons\s*-->|<!--\s*Mobile Navigation Buttons\s*-->\s*<div class="[^"]*sm:hidden[^"]*">/s')
            ->and($html)->toMatch('/<!--\s*Pagination Controls\s*-->\s*<div class="hidden sm:block">/');
    }
});

test('toast, empty-state, chip, and danger button use mobile-safe and high-contrast tokens', function () {
    $toast = (string) $this->blade('<x-toast />');
    expect($toast)->toContain('left-4 right-4 sm:left-auto')
        ->and($toast)->not->toContain('fixed bottom-4 right-4 z-50 flex flex-col space-y-2 pointer-events-none max-w-sm w-full');

    $empty = (string) $this->blade(
        '<x-empty-state title="Kosong" description="Deskripsi khusus kosong" action-label="Reset Filter" action-url="/articles" />'
    );
    expect($empty)->toContain('Deskripsi khusus kosong', 'Reset Filter', 'href="/articles"')
        ->and($empty)->not->toContain('description="', 'action-label="');

    $chip = (string) $this->blade('<x-chip variant="accent">Tech</x-chip>');
    expect($chip)->toContain('bg-accent text-on-accent')
        ->and($chip)->not->toContain('variant="accent"');

    $dangerBtn = (string) $this->blade('<x-button variant="danger">Hapus</x-button>');
    expect($dangerBtn)->toContain('bg-danger text-on-accent')
        ->and($dangerBtn)->not->toContain('text-white');
});

test('mobile drawers, admin media badge, editor toolbar, and login form maintain mobile ergonomics and dark mode contrast', function () {
    $publicHome = $this->get('/');
    $publicHome->assertOk()
        ->assertSee('overflow-y-auto', false)
        ->assertSee('backdrop-blur-sm', false)
        ->assertDontSee('backdrop-blur-xs', false);

    $loginRes = $this->get('/login');
    $loginRes->assertOk()
        ->assertDontSee('text-indigo-600', false)
        ->assertDontSee('dark:bg-gray-900', false);

    $user = User::factory()->create();

    $dashRes = $this->actingAs($user)->get('/dashboard');
    $dashRes->assertOk()
        ->assertSee('overflow-y-auto', false)
        ->assertDontSee('backdrop-blur-xs', false)
        ->assertDontSee('bg-accent-dark text-white', false);

    $editorRes = $this->actingAs($user)->get('/admin/articles/create');
    $editorRes->assertOk()
        ->assertSee('flex flex-wrap items-center gap-1', false)
        ->assertDontSee('backdrop-blur-xs', false);

    $media = Media::factory()->create(['user_id' => $user->id]);
    $mediaRes = $this->actingAs($user)->get('/admin/media');
    $mediaRes->assertOk()
        ->assertSee('bg-ink/90 text-surface', false)
        ->assertDontSee('bg-ink/80 text-white', false)
        ->assertDontSee('backdrop-blur-xs', false);
    expect($media->id)->toBeInt();
});
