<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Display the About page.
     */
    public function __invoke(): View
    {
        $aboutContent = (string) (Setting::get('about_content') ?? 'Selamat datang di NPblog. Blog personal minimalis dengan sentuhan sketsa hand-drawn.');
        $authorName = (string) (Setting::get('author_name') ?? 'Dapit');
        $authorBio = (string) (Setting::get('author_bio') ?? 'Software engineer yang menyukai kesederhanaan dan desain hand-drawn.');
        $socialLinks = (array) (Setting::get('social_links') ?? []);
        $profileImage = (string) (Setting::get('profile_image') ?? '');

        return view('public.about', compact('aboutContent', 'authorName', 'authorBio', 'socialLinks', 'profileImage'));
    }
}
