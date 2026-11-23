<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin overview dashboard with 4 stats, recent publications, and drafts to continue writing.
     */
    public function __invoke(): View
    {
        $totalArticles = Article::count();
        $publishedArticles = Article::published()->count();
        $draftArticles = Article::where('status', 'draft')->count();
        $totalCategories = Category::count();

        $recentPublished = Article::published()
            ->with('category')
            ->latest('published_at')
            ->take(5)
            ->get();

        $recentDrafts = Article::where('status', 'draft')
            ->with('category')
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalArticles',
            'publishedArticles',
            'draftArticles',
            'totalCategories',
            'recentPublished',
            'recentDrafts'
        ));
    }
}
