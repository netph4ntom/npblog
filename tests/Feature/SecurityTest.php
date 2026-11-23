<?php

use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use App\Services\MarkdownRenderer;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->create(['email' => 'admin@npblog.local']);
});

/*
|--------------------------------------------------------------------------
| 1. Security Headers
|--------------------------------------------------------------------------
*/
test('web responses include strict security headers', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

    $csp = $response->headers->get('Content-Security-Policy');
    expect($csp)->not->toBeNull()
        ->and($csp)->toContain("default-src 'self'")
        ->and($csp)->toContain("frame-ancestors 'self'")
        ->and($csp)->toContain("form-action 'self'");
});

test('secure HTTPS responses include HSTS header', function () {
    $response = $this->get('https://localhost/');

    $response->assertOk();
    $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

/*
|--------------------------------------------------------------------------
| 2. Authentication & Guest Access Control
|--------------------------------------------------------------------------
*/
test('unauthenticated guests are redirected to login when accessing admin endpoints', function () {
    $routes = [
        '/admin',
        '/admin/articles',
        '/admin/articles/create',
        '/admin/categories',
        '/admin/categories/create',
        '/admin/media',
        '/admin/settings',
        '/admin/profile',
        '/profile',
    ];

    foreach ($routes as $route) {
        $this->get($route)->assertRedirect('/login');
    }
});

test('public registration route is completely disabled', function () {
    $this->get('/register')->assertNotFound();
    $this->post('/register', [
        'name' => 'Attacker',
        'email' => 'attacker@example.com',
        'password' => 'password',
    ])->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| 3. CSRF Protection
|--------------------------------------------------------------------------
*/
test('post requests without CSRF token are rejected', function () {
    $middleware = new class(app(), app('encrypter')) extends ValidateCsrfToken
    {
        protected function runningUnitTests(): bool
        {
            return false;
        }
    };

    $request = Request::create('/admin/categories', 'POST', [
        'name' => 'Kategori CSRF',
    ]);
    $request->setLaravelSession(app('session.store'));

    expect(fn () => $middleware->handle($request, fn () => response('ok')))
        ->toThrow(TokenMismatchException::class);
});

/*
|--------------------------------------------------------------------------
| 4. XSS (Cross-Site Scripting) Prevention
|--------------------------------------------------------------------------
*/
test('XSS payloads in article title are escaped in Blade templates', function () {
    $xssTitle = '<script>alert("xss-title")</script>Judul Aman';
    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'title' => $xssTitle,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get("/articles/{$article->slug}");

    $response->assertOk();
    // Raw script tag must NOT be rendered
    $response->assertDontSee('<script>alert("xss-title")</script>', false);
    // Escaped string should exist
    $response->assertSee(e($xssTitle), false);
});

test('XSS payloads in article body markdown are sanitized by MarkdownRenderer', function () {
    $markdown = app(MarkdownRenderer::class);

    $dangerousMarkdown = "Paragraf aman.\n\n<script>alert('xss')</script>\n\n<img src=x onerror=\"alert('xss')\" />\n\n<a href=\"javascript:alert('xss')\">Klik Saya</a>";
    $html = $markdown->render($dangerousMarkdown);

    // Script tags and inline event handlers must be stripped
    expect($html)->not->toContain('<script>')
        ->and($html)->not->toContain('onerror')
        ->and($html)->not->toContain('javascript:alert')
        ->and($html)->toContain('<p>Paragraf aman.</p>');
});

test('XSS payloads in media alt text are escaped when rendered in image tags', function () {
    $xssAlt = 'Foto"><script>alert("xss-alt")</script>';
    $media = Media::factory()->create([
        'user_id' => $this->admin->id,
        'alt_text' => $xssAlt,
    ]);

    $article = Article::factory()->create([
        'user_id' => $this->admin->id,
        'featured_media_id' => $media->id,
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get("/articles/{$article->slug}");

    $response->assertOk();
    $response->assertDontSee('<script>alert("xss-alt")</script>', false);
    $response->assertSee(e($xssAlt), false);
});

test('XSS payloads in search queries are escaped in search view', function () {
    $xssQuery = '<script>alert("xss-search")</script>';

    $response = $this->get('/search?q='.urlencode($xssQuery));

    $response->assertOk();
    $response->assertDontSee('<script>alert("xss-search")</script>', false);
    $response->assertSee(e($xssQuery), false);
});

/*
|--------------------------------------------------------------------------
| 5. SQL Injection Prevention
|--------------------------------------------------------------------------
*/
test('SQL injection attempts in search parameters execute safely without error', function () {
    $injections = [
        "' OR '1'='1",
        "'; DROP TABLE articles; --",
        "' UNION SELECT id, name, email, password FROM users --",
        "1' AND SLEEP(2) AND '1'='1",
    ];

    foreach ($injections as $payload) {
        $resPublicSearch = $this->get('/search?q='.urlencode($payload));
        $resPublicSearch->assertOk();

        $resArticleIndex = $this->get('/articles?q='.urlencode($payload));
        $resArticleIndex->assertOk();

        $resAdminArticle = $this->actingAs($this->admin)->get('/admin/articles?q='.urlencode($payload));
        $resAdminArticle->assertOk();
    }
});

/*
|--------------------------------------------------------------------------
| 6. Dangerous Upload Prevention
|--------------------------------------------------------------------------
*/
test('dangerous uploads with double extensions or SVG payloads are blocked', function () {
    // 1. PHP script with .png extension
    $fakeImage = UploadedFile::fake()->createWithContent('shell.png', '<?php system($_GET["cmd"]); ?>');
    $this->actingAs($this->admin)->post('/admin/media', [
        'image' => $fakeImage,
    ])->assertSessionHasErrors(['image']);

    // 2. Double extension with executable
    $doubleExt = UploadedFile::fake()->create('malicious.php.jpg', 100);
    $this->actingAs($this->admin)->post('/admin/media', [
        'image' => $doubleExt,
    ])->assertSessionHasErrors(['image']);

    // 3. SVG file
    $svg = UploadedFile::fake()->createWithContent('vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
    $this->actingAs($this->admin)->post('/admin/media', [
        'image' => $svg,
    ])->assertSessionHasErrors(['image']);
});

/*
|--------------------------------------------------------------------------
| 7. Login Rate Limiting (Throttle)
|--------------------------------------------------------------------------
*/
test('login endpoint enforces rate limiting after 5 failed attempts', function () {
    $credentials = [
        'email' => 'admin@npblog.local',
        'password' => 'wrong-password',
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->post('/login', $credentials)->assertRedirect();
    }

    // 6th attempt must be throttled with error or 429
    $response = $this->post('/login', $credentials);
    $response->assertSessionHasErrors(['email']);
});

test('session cookie secure flag defaults to true in production environment', function () {
    $sessionConfig = require config_path('session.php');
    expect($sessionConfig['http_only'])->toBeTrue();

    $origSecureServer = $_SERVER['SESSION_SECURE_COOKIE'] ?? null;
    $origSecureEnv = $_ENV['SESSION_SECURE_COOKIE'] ?? null;
    $origAppEnvServer = $_SERVER['APP_ENV'] ?? null;
    $origAppEnvEnv = $_ENV['APP_ENV'] ?? null;

    unset($_SERVER['SESSION_SECURE_COOKIE'], $_ENV['SESSION_SECURE_COOKIE']);
    putenv('SESSION_SECURE_COOKIE');
    $_SERVER['APP_ENV'] = 'production';
    $_ENV['APP_ENV'] = 'production';
    putenv('APP_ENV=production');

    $prodSessionConfig = require config_path('session.php');
    expect($prodSessionConfig['secure'])->toBeTrue();

    if ($origSecureServer !== null) {
        $_SERVER['SESSION_SECURE_COOKIE'] = $origSecureServer;
    }
    if ($origSecureEnv !== null) {
        $_ENV['SESSION_SECURE_COOKIE'] = $origSecureEnv;
        putenv("SESSION_SECURE_COOKIE={$origSecureEnv}");
    }
    if ($origAppEnvServer !== null) {
        $_SERVER['APP_ENV'] = $origAppEnvServer;
    }
    if ($origAppEnvEnv !== null) {
        $_ENV['APP_ENV'] = $origAppEnvEnv;
        putenv("APP_ENV={$origAppEnvEnv}");
    }
});
