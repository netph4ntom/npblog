<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Generate dynamic sitemap.xml strictly excluding draft and future scheduled posts.
     */
    public function __invoke(): Response
    {
        $articles = Article::published()
            ->latest('updated_at')
            ->get();

        $content = view('public.sitemap', compact('articles'))->render();

        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
        ]);
    }
}
