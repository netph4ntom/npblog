<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        $rules = [
            'blog_name' => ['required', 'string', 'max:255'],
            'blog_tagline' => ['nullable', 'string', 'max:255'],
            'blog_description' => ['nullable', 'string', 'max:1000'],
            'logo' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'favicon' => ['nullable', 'file', 'mimes:ico,png,webp,jpg,jpeg', 'max:1024'],
            'hero_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'profile_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'author_bio' => ['nullable', 'string', 'max:1000'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'about_content' => ['nullable', 'string'],
            'seo_default_title' => ['nullable', 'string', 'max:255'],
            'seo_default_description' => ['nullable', 'string', 'max:500'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_favicon' => ['nullable', 'boolean'],
            'remove_hero_image' => ['nullable', 'boolean'],
            'remove_profile_image' => ['nullable', 'boolean'],
        ];

        foreach (array_keys(Setting::SOCIAL_PLATFORMS) as $platform) {
            $rules["social_links.{$platform}"] = ['nullable', 'string', 'url:http,https', 'max:255'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $attributes = [
            'blog_name' => 'nama blog',
            'contact_email' => 'email kontak',
            'logo' => 'logo blog',
            'favicon' => 'favicon',
            'hero_image' => 'hero banner',
            'profile_image' => 'foto profil',
        ];

        foreach (Setting::SOCIAL_PLATFORMS as $platform => $label) {
            $attributes["social_links.{$platform}"] = "tautan {$label}";
        }

        return $attributes;
    }
}
