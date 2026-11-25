<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the home page with hero, featured article, latest posts, and about preview.
     */
    public function __invoke(): View
    {
        $featured = Article::published()
            ->with(['category', 'tags', 'featuredMedia', 'user'])
            ->featured()
            ->latest('published_at')
            ->first();

        $latestQuery = Article::published()
            ->with(['category', 'tags', 'featuredMedia', 'user']);

        if ($featured !== null) {
            $latestQuery->where('id', '!=', $featured->id);
        }

        $latest = $latestQuery->latest('published_at')
            ->take(6)
            ->get();

        $authorBio = Setting::getString('author_bio', 'Software engineer yang menyukai kesederhanaan dan desain hand-drawn.');
        $authorName = Setting::getString('author_name', 'Dapit');
        $blogTagline = Setting::getString('blog_tagline', 'Catatan Kode & Arsitektur Minimalis');
        $heroImage = Setting::getString('hero_image');
        $profileImage = Setting::getString('profile_image');

        return view('public.home', compact('featured', 'latest', 'authorBio', 'authorName', 'blogTagline', 'heroImage', 'profileImage'));
    }
}
