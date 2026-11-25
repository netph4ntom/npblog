<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * Supported social media platforms shown in Settings and public pages.
     *
     * @var array<string, string>
     */
    public const array SOCIAL_PLATFORMS = [
        'github' => 'GitHub',
        'twitter' => 'X / Twitter',
        'linkedin' => 'LinkedIn',
        'youtube' => 'YouTube',
        'website' => 'Website',
    ];

    /**
     * Setting keys that reference Media gallery records.
     *
     * @var list<string>
     */
    public const array IMAGE_KEYS = [
        'logo',
        'hero_image',
        'profile_image',
    ];

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'key';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->find($key);

        if (! $setting || $setting->value === null) {
            return $default;
        }

        $decoded = json_decode((string) $setting->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
    }

    /**
     * Get a non-empty string setting value with fallback when blank or non-string.
     */
    public static function getString(string $key, string $default = ''): string
    {
        $value = static::get($key);

        if (is_string($value)) {
            return trim($value) !== '' ? $value : $default;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $default;
    }

    /**
     * Return validated HTTP/HTTPS social links keyed by platform metadata.
     *
     * @return array<string, array{label: string, url: string}>
     */
    public static function safeSocialLinks(): array
    {
        $rawLinks = static::get('social_links', []);
        if (! is_array($rawLinks)) {
            return [];
        }

        $validated = [];
        foreach (self::SOCIAL_PLATFORMS as $platform => $label) {
            $url = $rawLinks[$platform] ?? null;
            if (! is_string($url)) {
                continue;
            }

            $trimmed = trim($url);
            if ($trimmed === '' || filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
                continue;
            }

            $scheme = strtolower((string) parse_url($trimmed, PHP_URL_SCHEME));
            if (! in_array($scheme, ['http', 'https'], true)) {
                continue;
            }

            $validated[$platform] = [
                'label' => $label,
                'url' => $trimmed,
            ];
        }

        return $validated;
    }

    /**
     * Extract relative storage disk path from a setting media URL.
     */
    public static function extractStoragePath(?string $url): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $cleanPath = ltrim(preg_replace('#^.*?/storage/#', '', trim($url)) ?? '', '/');

        return $cleanPath !== '' ? $cleanPath : null;
    }

    /**
     * Set a setting key-value pair safely preserving scalar/string types.
     */
    public static function set(string $key, mixed $value): self
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $encoded === false ? '' : $encoded]
        );
    }
}
