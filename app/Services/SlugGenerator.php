<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * Reserved slugs that clash with core application routes and assets.
     *
     * @var list<string>
     */
    public const RESERVED_SLUGS = [
        'about',
        'admin',
        'api',
        'archive',
        'articles',
        'categories',
        'category',
        'dashboard',
        'feed',
        'feed.xml',
        'home',
        'index',
        'login',
        'logout',
        'media',
        'profile',
        'register',
        'robots.txt',
        'search',
        'settings',
        'sitemap',
        'sitemap.xml',
        'tags',
    ];

    /**
     * Generate a unique, lowercase slug with numeric collision suffixing.
     *
     * @param  class-string<Model>  $modelClass
     */
    public function generate(
        string $title,
        ?int $ignoreId = null,
        string $modelClass = Article::class,
        string $column = 'slug'
    ): string {
        $slug = Str::slug($title);

        if ($slug === '') {
            $slug = 'post';
        }

        $baseSlug = $slug;

        // Check if the base slug is already unique and not reserved
        if (! $this->isReserved($baseSlug) && ! $this->existsInDatabase($baseSlug, $ignoreId, $modelClass, $column)) {
            return $baseSlug;
        }

        // Fetch existing slugs starting with baseSlug
        $query = $modelClass::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            /** @phpstan-ignore-next-line */
            $query->withTrashed();
        }

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        /** @var Collection<int, string> $existing */
        $existing = $query->where($column, 'LIKE', "{$baseSlug}%")
            ->pluck($column);

        $existingSlugs = $existing->all();

        $suffix = 2;
        while (
            in_array("{$baseSlug}-{$suffix}", $existingSlugs, true)
            || $this->isReserved("{$baseSlug}-{$suffix}")
        ) {
            $suffix++;
        }

        return "{$baseSlug}-{$suffix}";
    }

    /**
     * Determine if a given slug is reserved.
     */
    public function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), self::RESERVED_SLUGS, true);
    }

    /**
     * Check if a slug already exists in the given model.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function existsInDatabase(string $slug, ?int $ignoreId, string $modelClass, string $column): bool
    {
        $query = $modelClass::query()->where($column, $slug);

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            /** @phpstan-ignore-next-line */
            $query->withTrashed();
        }

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }
}
