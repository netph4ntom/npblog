<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\View\View;

class OfflineController extends Controller
{
    /**
     * Render a lightweight offline fallback page for PWA navigation.
     */
    public function __invoke(): View
    {
        return view('public.offline', [
            'blogName' => Setting::getString('blog_name', 'NPblog'),
            'faviconUrl' => Setting::getString('favicon'),
        ]);
    }
}

