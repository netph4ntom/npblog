<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreArticleRequest;
use App\Http\Requests\Admin\UpdateArticleRequest;
use App\Models\Article;
use App\Models\Category;
use App\Models\Redirect;
use App\Models\Tag;
use App\Models\User;
use App\Services\ExcerptGenerator;
use App\Services\MarkdownRenderer;
use App\Services\ReadingTime;
use App\Services\SlugGenerator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display a listing of the articles with filters and search.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $categoryId = $request->query('category_id');
        $search = $request->query('q');

        $query = Article::query()->with(['category', 'tags'])->latest('created_at');

        if ($status === 'published') {
            $query->published();
        } elseif ($status === 'draft') {
            $query->where('status', 'draft');
        }

        if (is_numeric($categoryId) && (int) $categoryId > 0) {
            $query->where('category_id', (int) $categoryId);
        }

        if (is_string($search) && trim($search) !== '') {
            $query->search($search);
        }

        $articles = $query->paginate(15)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.articles.index', compact('articles', 'categories', 'status', 'categoryId', 'search'));
    }

    /**
     * Show the form for creating a new article.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.articles.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created article in storage.
     */
    public function store(
        StoreArticleRequest $request,
        SlugGenerator $slugGen,
        ReadingTime $readingTime,
        ExcerptGenerator $excerptGen,
        MarkdownRenderer $markdown
    ): RedirectResponse {
        $validated = $request->validated();

        $slugInput = (string) ($validated['slug'] ?? $validated['title']);
        $slug = $slugGen->generate($slugInput, modelClass: Article::class);

        $body = (string) $validated['body'];
        $bodyHtml = $markdown->render($body);
        $excerpt = $excerptGen->generate($validated['excerpt'] ?? null, $body);
        $metrics = $readingTime->analyze($body);

        $status = (string) $validated['status'];
        $publishedAt = null;

        if ($status === 'published') {
            $publishedAt = ! empty($validated['published_at'])
                ? Carbon::parse((string) $validated['published_at'])
                : now();
        }

        /** @var User $user */
        $user = $request->user();

        $article = Article::create([
            'user_id' => $user->id,
            'category_id' => $validated['category_id'] ?? null,
            'title' => (string) $validated['title'],
            'slug' => $slug,
            'excerpt' => $excerpt,
            'body' => $body,
            'body_html' => $bodyHtml,
            'status' => $status,
            'published_at' => $publishedAt,
            'is_featured' => $request->boolean('is_featured'),
            'word_count' => $metrics['word_count'],
            'reading_time' => $metrics['reading_time'],
        ]);

        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $article->tags()->sync($validated['tags']);
        }

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diterbitkan.');
    }

    /**
     * Show the form for editing the specified article.
     */
    public function edit(Article $article): View
    {
        $article->load(['category', 'tags']);
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.articles.edit', compact('article', 'categories', 'tags'));
    }

    /**
     * Update the specified article in storage.
     */
    public function update(
        UpdateArticleRequest $request,
        Article $article,
        SlugGenerator $slugGen,
        ReadingTime $readingTime,
        ExcerptGenerator $excerptGen,
        MarkdownRenderer $markdown
    ): RedirectResponse {
        $validated = $request->validated();

        $slugInput = (string) ($validated['slug'] ?? $validated['title']);
        $newSlug = $slugGen->generate($slugInput, ignoreId: $article->id, modelClass: Article::class);

        // Record 301 redirect if slug changed on published article
        if ($newSlug !== $article->slug && $article->status === 'published') {
            Redirect::firstOrCreate([
                'from_slug' => $article->slug,
            ], [
                'article_id' => $article->id,
            ]);
        }

        $body = (string) $validated['body'];
        $bodyHtml = $markdown->render($body);
        $excerpt = $excerptGen->generate($validated['excerpt'] ?? null, $body);
        $metrics = $readingTime->analyze($body);

        $status = (string) $validated['status'];
        $publishedAt = null;

        if ($status === 'published') {
            $publishedAt = ! empty($validated['published_at'])
                ? Carbon::parse((string) $validated['published_at'])
                : ($article->published_at ?? now());
        }

        $article->update([
            'category_id' => $validated['category_id'] ?? null,
            'title' => (string) $validated['title'],
            'slug' => $newSlug,
            'excerpt' => $excerpt,
            'body' => $body,
            'body_html' => $bodyHtml,
            'status' => $status,
            'published_at' => $publishedAt,
            'is_featured' => $request->boolean('is_featured'),
            'word_count' => $metrics['word_count'],
            'reading_time' => $metrics['reading_time'],
        ]);

        $tags = isset($validated['tags']) && is_array($validated['tags']) ? $validated['tags'] : [];
        $article->tags()->sync($tags);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil diperbarui.');
    }

    /**
     * Remove the specified article from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.articles.index')->with('success', 'Artikel berhasil dihapus.');
    }

    /**
     * Render a server-side preview of markdown.
     */
    public function preview(Request $request, MarkdownRenderer $markdown): JsonResponse
    {
        $body = (string) $request->input('body', '');
        $html = $markdown->render($body);

        return response()->json([
            'html' => $html,
        ]);
    }
}
