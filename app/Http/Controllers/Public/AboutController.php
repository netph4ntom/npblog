<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\MarkdownRenderer;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Display the About page.
     */
    public function __invoke(MarkdownRenderer $markdownRenderer): View
    {
        $aboutContent = Setting::getString('about_content', 'Selamat datang di NPblog. Blog personal minimalis dengan sentuhan sketsa hand-drawn.');
        $aboutHtml = $markdownRenderer->render($aboutContent);
        $authorName = Setting::getString('author_name', 'Dapit');
        $authorBio = Setting::getString('author_bio', 'Software engineer yang menyukai kesederhanaan dan desain hand-drawn.');
        $socialLinks = Setting::safeSocialLinks();
        $profileImage = Setting::getString('profile_image');
        $contactEmail = Setting::getString('contact_email');

        return view('public.about', compact('aboutHtml', 'authorName', 'authorBio', 'socialLinks', 'profileImage', 'contactEmail'));
    }
}
