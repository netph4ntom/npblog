<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use App\Services\MediaProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Media upload keys mapped to default alt texts.
     *
     * @var array<string, string>
     */
    protected const array MEDIA_UPLOAD_SLOTS = [
        'logo' => 'Logo Blog',
        'hero_image' => 'Hero Banner Blog',
        'profile_image' => 'Foto Profil Penulis',
    ];

    /**
     * Show the form for editing blog settings.
     */
    public function edit(): View
    {
        $rawSocial = Setting::get('social_links', []);
        $stringSetting = static function (string $key, string $default): string {
            $val = Setting::get($key, $default);

            return is_scalar($val) ? (string) $val : $default;
        };

        $settings = [
            'blog_name' => $stringSetting('blog_name', 'NPblog'),
            'blog_tagline' => $stringSetting('blog_tagline', 'Catatan Kode & Arsitektur Minimalis'),
            'blog_description' => $stringSetting('blog_description', 'Blog personal minimalis bergaya hand-drawn oleh Dapit.'),
            'logo' => $stringSetting('logo', ''),
            'favicon' => $stringSetting('favicon', ''),
            'hero_image' => $stringSetting('hero_image', ''),
            'author_name' => $stringSetting('author_name', 'Dapit'),
            'author_bio' => $stringSetting('author_bio', 'Software engineer yang menyukai kesederhanaan dan desain hand-drawn.'),
            'profile_image' => $stringSetting('profile_image', ''),
            'contact_email' => $stringSetting('contact_email', ''),
            'social_links' => is_array($rawSocial) ? $rawSocial : [],
            'about_content' => $stringSetting('about_content', "Selamat datang di NPblog. Blog personal minimalis dengan sentuhan sketsa hand-drawn.\n\nDitulis oleh Dapit."),
            'seo_default_title' => $stringSetting('seo_default_title', 'NPblog — Catatan Dapit'),
            'seo_default_description' => $stringSetting('seo_default_description', 'Blog personal minimalis bergaya hand-drawn oleh Dapit.'),
        ];

        return view('admin.settings.edit', compact('settings'));
    }

    /**
     * Update the blog settings.
     */
    public function update(UpdateSettingsRequest $request, MediaProcessor $mediaProcessor): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        // 1. Process all uploaded files first before mutating existing settings/files
        $stagedUrls = [];
        /** @var list<Media> $stagedMediaModels */
        $stagedMediaModels = [];
        /** @var list<string> $stagedFaviconPaths */
        $stagedFaviconPaths = [];
        $currentField = 'logo';

        try {
            foreach (self::MEDIA_UPLOAD_SLOTS as $key => $altText) {
                if ($request->hasFile($key)) {
                    $currentField = $key;
                    /** @var UploadedFile $file */
                    $file = $request->file($key);
                    $media = $mediaProcessor->process($file, $user, $altText);
                    $stagedMediaModels[] = $media;
                    $stagedUrls[$key] = $media->url;
                }
            }

            if ($request->hasFile('favicon')) {
                $currentField = 'favicon';
                /** @var UploadedFile $favFile */
                $favFile = $request->file('favicon');
                $favUrl = $mediaProcessor->processFavicon($favFile);
                $favPath = Setting::extractStoragePath($favUrl);
                if ($favPath !== null) {
                    $stagedFaviconPaths[] = $favPath;
                }
                $stagedUrls['favicon'] = $favUrl;
            }
        } catch (\InvalidArgumentException $e) {
            foreach ($stagedMediaModels as $media) {
                $media->delete();
            }
            foreach ($stagedFaviconPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            return back()->withInput()->withErrors([$currentField => $e->getMessage()]);
        }

        // 2. Persist text/string settings
        $textKeys = [
            'blog_name',
            'blog_tagline',
            'blog_description',
            'author_name',
            'author_bio',
            'contact_email',
            'about_content',
            'seo_default_title',
            'seo_default_description',
        ];

        foreach ($textKeys as $key) {
            if ($request->has($key)) {
                $val = $request->input($key);
                Setting::set($key, is_string($val) ? trim($val) : '');
            }
        }

        // 3. Apply new media URLs or removals and clean up replaced/removed files
        $allImageKeys = array_merge(array_keys(self::MEDIA_UPLOAD_SLOTS), ['favicon']);
        foreach ($allImageKeys as $key) {
            if (isset($stagedUrls[$key])) {
                $this->deleteSettingMedia((string) Setting::get($key));
                Setting::set($key, $stagedUrls[$key]);
            } elseif ($request->boolean("remove_{$key}")) {
                $this->deleteSettingMedia((string) Setting::get($key));
                Setting::set($key, '');
            }
        }

        // 4. Persist whitelisted social links array
        if ($request->has('social_links')) {
            $rawSocial = (array) $request->input('social_links', []);
            $socialLinks = [];
            foreach (array_keys(Setting::SOCIAL_PLATFORMS) as $network) {
                $url = $rawSocial[$network] ?? null;
                if (is_string($url) && trim($url) !== '') {
                    $socialLinks[$network] = trim($url);
                }
            }
            Setting::set('social_links', $socialLinks);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan blog berhasil diperbarui.');
    }

    /**
     * Delete existing media and associated storage files when removed or replaced.
     */
    protected function deleteSettingMedia(?string $url): void
    {
        $cleanPath = Setting::extractStoragePath($url);
        if ($cleanPath === null) {
            return;
        }

        $media = Media::where('path', $cleanPath)->first();
        if ($media) {
            $media->delete();
        } elseif (Storage::disk('public')->exists($cleanPath)) {
            Storage::disk('public')->delete($cleanPath);
        }
    }
}
