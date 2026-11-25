<?php

use App\Models\Article;
use App\Models\Category;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'name' => 'Dapit',
        'email' => 'dapit@npblog.local',
    ]);

    $this->techCategory = Category::factory()->create([
        'name' => 'Technology',
        'slug' => 'technology',
    ]);

    $this->secCategory = Category::factory()->create([
        'name' => 'Cybersecurity',
        'slug' => 'cybersecurity',
    ]);

    $this->laravelTag = Tag::factory()->create([
        'name' => 'Laravel',
        'slug' => 'laravel',
    ]);

    // Setup blog settings
    Setting::set('blog_name', 'NPblog');
    Setting::set('blog_tagline', 'Catatan Kode & Arsitektur Minimalis');
    Setting::set('author_name', 'Dapit');
    Setting::set('author_bio', 'Software engineer yang menyukai kesederhanaan dan desain hand-drawn.');
    Setting::set('about_content', 'Ini adalah halaman About lengkap tentang Dapit dan misi NPblog.');
});

test('home page renders hero, featured, latest articles, and about preview', function () {
    $featured = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Artikel Unggulan Pertama',
        'slug' => 'artikel-unggulan-pertama',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'is_featured' => true,
    ]);

    $latest = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->secCategory->id,
        'title' => 'Artikel Terbaru Biasa',
        'slug' => 'artikel-terbaru-biasa',
        'status' => 'published',
        'published_at' => now()->subHours(2),
        'is_featured' => false,
    ]);

    $draft = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Artikel Draft Rahasia',
        'slug' => 'artikel-draft-rahasia',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->get('/');

    $response->assertOk()
        ->assertSee('Artikel Unggulan Pertama')
        ->assertSee('Artikel Terbaru Biasa')
        ->assertDontSee('Artikel Draft Rahasia')
        ->assertSee('Dapit')
        ->assertSee('Software engineer');
});

test('articles index displays published articles and category chips', function () {
    Article::factory()->count(5)->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'status' => 'published',
        'published_at' => now()->subDays(2),
    ]);

    $response = $this->get('/articles');

    $response->assertOk()
        ->assertSee('Technology')
        ->assertSee('Cybersecurity')
        ->assertViewHas('articles');
});

test('articles index filters by category slug', function () {
    $techArticle = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Deep Dive Linux Container',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $secArticle = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->secCategory->id,
        'title' => 'Praktik Keamanan Web Modern',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/articles?category=technology');

    $response->assertOk()
        ->assertSee('Deep Dive Linux Container')
        ->assertDontSee('Praktik Keamanan Web Modern');
});

test('articles index filters by search query and preserves parameters in pagination', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Eksplorasi Kernel Linux',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Tutorial Belajar CSS Grid',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/articles?q=Kernel');

    $response->assertOk()
        ->assertSee('Eksplorasi Kernel Linux')
        ->assertDontSee('Tutorial Belajar CSS Grid');
});

test('article detail displays published article content and metadata', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Panduan Hand-Drawn Design',
        'slug' => 'panduan-hand-drawn-design',
        'body' => 'Isi artikel lengkap...',
        'body_html' => '<p>Isi artikel berformat HTML dengan gaya <strong>hand-drawn</strong>.</p>',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'word_count' => 350,
        'reading_time' => 2,
    ]);
    $article->tags()->attach($this->laravelTag);

    $response = $this->get('/articles/panduan-hand-drawn-design');

    $response->assertOk()
        ->assertSee('Panduan Hand-Drawn Design')
        ->assertSee('Technology')
        ->assertSee('Laravel')
        ->assertSee('2 min')
        ->assertSee('Isi artikel berformat HTML', false);
});

test('article detail returns 404 for draft article', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Draft Tersembunyi',
        'slug' => 'draft-tersembunyi',
        'status' => 'draft',
        'published_at' => null,
    ]);

    $response = $this->get('/articles/draft-tersembunyi');

    $response->assertNotFound();
});

test('article detail returns 404 for scheduled article with future publish date', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Artikel Masa Depan',
        'slug' => 'artikel-masa-depan',
        'status' => 'published',
        'published_at' => now()->addDays(5),
    ]);

    $response = $this->get('/articles/artikel-masa-depan');

    $response->assertNotFound();
});

test('article detail returns 301 redirect for old slug in redirects table', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Judul Artikel Baru',
        'slug' => 'judul-artikel-baru',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Redirect::create([
        'article_id' => $article->id,
        'from_slug' => 'judul-artikel-lama',
    ]);

    $response = $this->get('/articles/judul-artikel-lama');

    $response->assertStatus(301)
        ->assertRedirect('/articles/judul-artikel-baru');
});

test('redirect returns 404 if target article is not published', function () {
    $article = Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Draft Belum Siap',
        'slug' => 'draft-belum-siap',
        'status' => 'draft',
        'published_at' => null,
    ]);

    Redirect::create([
        'article_id' => $article->id,
        'from_slug' => 'slug-lama-ke-draft',
    ]);

    $response = $this->get('/articles/slug-lama-ke-draft');

    $response->assertNotFound();
});

test('about page displays settings content', function () {
    $response = $this->get('/about');

    $response->assertOk()
        ->assertSee('Dapit')
        ->assertSee('Ini adalah halaman About lengkap tentang Dapit dan misi NPblog.');
});

test('public search page is removed and returns 404 without nav link', function () {
    $this->get('/')->assertOk()->assertDontSee(url('/search'), false);
    $this->get('/search')->assertNotFound();
});

test('articles index search handles short queries under 3 characters', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Belajar Bahasa Go Pemula',
        'slug' => 'belajar-bahasa-go-pemula',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/articles?q=Go');

    $response->assertOk()
        ->assertSee('Belajar Bahasa Go Pemula');
});

test('themed 404 page renders hand-drawn error elements', function () {
    $response = $this->get('/halaman-yang-pasti-tidak-ada-404');

    $response->assertNotFound()
        ->assertSee('404')
        ->assertSee('Halaman Tidak Ditemukan');
});

test('article detail renders code blocks with language metadata and code-block class', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Tutorial Snippet Kode',
        'slug' => 'tutorial-snippet-kode',
        'body_html' => '<pre class="code-block hljs language-php" data-language="php"><code class="hljs language-php"><span class="hljs-keyword">echo</span> 1;</code></pre>',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/articles/tutorial-snippet-kode');

    $response->assertOk()
        ->assertSee('class="code-block', false)
        ->assertSee('data-language="php"', false);
});

test('themed 500 page renders hand-drawn error elements', function () {
    $view = view('errors.500')->render();

    expect($view)->toContain('500')
        ->and($view)->toContain('Terjadi Kesalahan Server');
});

test('article detail eager loads category and media for related articles', function () {
    $mainArticle = Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Main Article',
        'slug' => 'main-article',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    Article::factory()->count(2)->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/articles/main-article');
    $response->assertOk();

    $related = $response->viewData('related');
    expect($related)->not->toBeEmpty();
    foreach ($related as $rel) {
        expect($rel->relationLoaded('category'))->toBeTrue()
            ->and($rel->relationLoaded('featuredMedia'))->toBeTrue();
    }
});

test('home page eager loads user relationship on featured and latest articles', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Featured Post',
        'is_featured' => true,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/');
    $response->assertOk();

    $featured = $response->viewData('featured');
    expect($featured)->not->toBeNull()
        ->and($featured->relationLoaded('user'))->toBeTrue();
});

test('public pages integrate settings for brand, hero, profile, contact, social links, markdown about, and seo description fallback', function () {
    Setting::set('blog_name', 'Blog Dapit');
    Setting::set('blog_description', 'Deskripsi Blog Utama');
    Setting::set('seo_default_description', '');
    Setting::set('logo', '/storage/media/brand-logo.webp');
    Setting::set('favicon', '/storage/media/brand-fav.webp');
    Setting::set('hero_image', '/storage/media/home-hero.webp');
    Setting::set('profile_image', '/storage/media/author-avatar.webp');
    Setting::set('contact_email', 'kontak@dapit.dev');
    Setting::set('social_links', [
        'github' => 'https://github.com/dapit-dev',
        'twitter' => 'https://x.com/dapitdev',
    ]);
    Setting::set('about_content', "## Judul Tentang\n\nParagraf **tebal** dan [tautan](https://example.com).");

    $homeRes = $this->get('/');
    $homeRes->assertOk()
        ->assertSee('Blog Dapit')
        ->assertSee('/storage/media/brand-logo.webp', false)
        ->assertSee('/storage/media/brand-fav.webp', false)
        ->assertSee('/storage/media/home-hero.webp', false)
        ->assertSee('/storage/media/author-avatar.webp', false)
        ->assertSee('<meta name="description" content="Deskripsi Blog Utama">', false)
        ->assertSee('https://github.com/dapit-dev', false);

    $aboutRes = $this->get('/about');
    $aboutRes->assertOk()
        ->assertSee('Judul Tentang')
        ->assertSee('<strong>tebal</strong>', false)
        ->assertSee('mailto:kontak@dapit.dev', false)
        ->assertSee('https://github.com/dapit-dev', false)
        ->assertSee('https://x.com/dapitdev', false)
        ->assertSee('/storage/media/author-avatar.webp', false)
        ->assertDontSee('mailto:admin@npblog.local', false);
});

test('articles index renders category filter as accessible hand-drawn dropdown preserving search query', function () {
    Article::factory()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->techCategory->id,
        'title' => 'Artikel Dropdown Test',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/articles?category=technology&q=Dropdown');

    $response->assertOk()
        ->assertSee('aria-label="Filter kategori artikel"', false)
        ->assertSee('role="menu"', false)
        ->assertSee('Technology (1)')
        ->assertSee('category=cybersecurity&amp;q=Dropdown', false)
        ->assertSee('Reset Filter');
});
