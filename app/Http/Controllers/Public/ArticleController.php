<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use App\Models\Redirect;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display a listing of published articles with category and search filters.
     */
    public function index(Request $request): View
    {
        $categorySlug = $request->query('category');
        $search = $request->query('q');

        $query = Article::published()
            ->with(['category', 'tags', 'featuredMedia'])
            ->latest('published_at');

        if (is_string($categorySlug) && $categorySlug !== '') {
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if (is_string($search) && $search !== '') {
            $query = $query->search($search);
        }

        $perPage = max(1, min(50, (int) Setting::get('posts_per_page', 9)));
        $articles = $query->paginate($perPage)->withQueryString();

        $categories = Category::query()
            ->withCount(['articles' => function ($q) {
                $q->published();
            }])
            ->orderBy('name')
            ->get();

        return view('public.articles.index', compact('articles', 'categories', 'categorySlug', 'search'));
    }

    /**
     * Display a single published article or redirect if old slug exists.
     */
    public function show(string $slug): View|RedirectResponse
    {
        $article = Article::published()
            ->with(['category', 'tags', 'featuredMedia', 'user'])
            ->where('slug', $slug)
            ->first();

        if ($article === null) {
            // Check for 301 redirects
            $redirect = Redirect::query()
                ->with('article')
                ->where('from_slug', $slug)
                ->first();

            if (
                $redirect !== null
                && $redirect->article !== null
                && $redirect->article->status === 'published'
                && $redirect->article->published_at !== null
                && $redirect->article->published_at->lte(now())
            ) {
                return redirect()->route('articles.show', $redirect->article->slug, 301);
            }

            abort(404);
        }

        $related = Article::published()
            ->with(['category', 'featuredMedia', 'tags'])
            ->where('id', '!=', $article->id)
            ->when($article->category_id !== null, function ($q) use ($article) {
                $q->where('category_id', $article->category_id);
            })
            ->latest('published_at')
            ->take(3)
            ->get();

        $prev = Article::published()
            ->where('published_at', '<', $article->published_at)
            ->latest('published_at')
            ->first();

        $next = Article::published()
            ->where('published_at', '>', $article->published_at)
            ->oldest('published_at')
            ->first();

        return view('public.articles.show', compact('article', 'related', 'prev', 'next'));
    }
}
