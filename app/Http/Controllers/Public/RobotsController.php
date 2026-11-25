<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Generate dynamic robots.txt pointing to sitemap.xml and disallowing private areas.
     */
    public function __invoke(): Response
    {
        $sitemapUrl = route('sitemap');

        $content = implode("\n", [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /login',
            'Disallow: /profile',
            '',
            "Sitemap: {$sitemapUrl}",
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
