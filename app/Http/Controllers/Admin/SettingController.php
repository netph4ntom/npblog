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
     * Show the form for editing blog settings.
     */
    public function edit(): View
    {
        $settings = [
            'blog_name' => Setting::get('blog_name', 'NPblog'),
            'blog_tagline' => Setting::get('blog_tagline', 'Catatan Kode & Arsitektur Minimalis'),
            'blog_description' => Setting::get('blog_description', 'Blog personal minimalis bergaya hand-drawn oleh Dapit.'),
            'logo' => Setting::get('logo', ''),
            'favicon' => Setting::get('favicon', ''),
            'hero_image' => Setting::get('hero_image', ''),
            'author_name' => Setting::get('author_name', 'Dapit'),
            'author_bio' => Setting::get('author_bio', 'Software engineer yang menyukai kesederhanaan dan desain hand-drawn.'),
            'profile_image' => Setting::get('profile_image', ''),
            'contact_email' => Setting::get('contact_email', ''),
            'social_links' => (array) Setting::get('social_links', []),
            'about_content' => Setting::get('about_content', "Selamat datang di NPblog. Blog personal minimalis dengan sentuhan sketsa hand-drawn.\n\nDitulis oleh Dapit."),
            'seo_default_title' => Setting::get('seo_default_title', 'NPblog — Catatan Dapit'),
            'seo_default_description' => Setting::get('seo_default_description', 'Blog personal minimalis bergaya hand-drawn oleh Dapit.'),
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

        // 1. Update text/string settings
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

        // 2. Handle image removals
        if ($request->boolean('remove_logo')) {
            $this->deleteSettingMedia((string) Setting::get('logo'));
            Setting::set('logo', '');
        }
        if ($request->boolean('remove_favicon')) {
            $this->deleteSettingMedia((string) Setting::get('favicon'));
            Setting::set('favicon', '');
        }
        if ($request->boolean('remove_hero_image')) {
            $this->deleteSettingMedia((string) Setting::get('hero_image'));
            Setting::set('hero_image', '');
        }
        if ($request->boolean('remove_profile_image')) {
            $this->deleteSettingMedia((string) Setting::get('profile_image'));
            Setting::set('profile_image', '');
        }

        // 3. Handle image uploads via MediaProcessor
        if ($request->hasFile('logo')) {
            $this->deleteSettingMedia((string) Setting::get('logo'));
            /** @var UploadedFile $logoFile */
            $logoFile = $request->file('logo');
            $media = $mediaProcessor->process($logoFile, $user, 'Logo Blog');
            Setting::set('logo', $media->url);
        }

        if ($request->hasFile('hero_image')) {
            $this->deleteSettingMedia((string) Setting::get('hero_image'));
            /** @var UploadedFile $heroFile */
            $heroFile = $request->file('hero_image');
            $media = $mediaProcessor->process($heroFile, $user, 'Hero Banner Blog');
            Setting::set('hero_image', $media->url);
        }

        if ($request->hasFile('profile_image')) {
            $this->deleteSettingMedia((string) Setting::get('profile_image'));
            /** @var UploadedFile $profileFile */
            $profileFile = $request->file('profile_image');
            $media = $mediaProcessor->process($profileFile, $user, 'Foto Profil Penulis');
            Setting::set('profile_image', $media->url);
        }

        if ($request->hasFile('favicon')) {
            $this->deleteSettingMedia((string) Setting::get('favicon'));
            /** @var UploadedFile $favFile */
            $favFile = $request->file('favicon');
            $ext = $favFile->getClientOriginalExtension() ?: 'ico';
            $path = $favFile->storeAs('media', 'favicon.'.$ext, 'public');
            Setting::set('favicon', Storage::disk('public')->url($path));
        }

        // 4. Handle social links array
        if ($request->has('social_links')) {
            $rawSocial = (array) $request->input('social_links', []);
            $socialLinks = [];
            foreach ($rawSocial as $network => $url) {
                if (is_string($url) && trim($url) !== '') {
                    $socialLinks[(string) $network] = trim($url);
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
        if (empty($url)) {
            return;
        }

        $cleanPath = ltrim(preg_replace('#^.*?/storage/#', '', $url) ?? '', '/');
        if ($cleanPath === '') {
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
