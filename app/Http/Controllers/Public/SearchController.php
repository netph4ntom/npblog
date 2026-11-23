<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display the search results page.
     */
    public function __invoke(Request $request): View
    {
        $query = $request->query('q');
        $searchTerm = is_string($query) ? trim($query) : '';

        $articles = null;
        if ($searchTerm !== '') {
            $articles = Article::published()
                ->with(['category', 'tags', 'featuredMedia'])
                ->search($searchTerm)
                ->latest('published_at')
                ->paginate(9)
                ->withQueryString();
        }

        return view('public.search', compact('articles', 'searchTerm'));
    }
}
