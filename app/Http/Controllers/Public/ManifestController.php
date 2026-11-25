<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ManifestController extends Controller
{
    /**
     * Serve the Web App Manifest using configured blog settings.
     */
    public function __invoke(): JsonResponse
    {
        $blogName = Setting::getString('blog_name', 'NPblog');
        $blogDescription = Setting::getString(
            'blog_description',
            'Blog personal minimalis bergaya hand-drawn oleh Dapit.'
        );
        $shortName = trim(Str::limit($blogName, 12, ''));

        $manifest = [
            'id' => '/',
            'name' => $blogName,
            'short_name' => $shortName !== '' ? $shortName : 'NPblog',
            'description' => $blogDescription,
            'lang' => 'id',
            'dir' => 'ltr',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => '#F6F6F3',
            'theme_color' => '#F6F6F3',
            'icons' => [
                [
                    'src' => '/icons/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/icons/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/icons/icon-maskable-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
                [
                    'src' => '/icons/icon-maskable-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];

        return response()
            ->json($manifest, 200, [
                'Content-Type' => 'application/manifest+json; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
    }
}
