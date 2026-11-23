{!! '<' . '?xml version="1.0" encoding="UTF-8"?' . '>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ route('home') }}</loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ route('articles.index') }}</loc>
        <changefreq>daily</changefreq>
        <priority>0.9</priority>
    </url>
    <url>
        <loc>{{ route('about') }}</loc>
        <changefreq>monthly</changefreq>
        <priority>0.7</priority>
    </url>
    @foreach($articles as $article)
        <url>
            <loc>{{ route('articles.show', $article->slug) }}</loc>
            <lastmod>{{ $article->updated_at?->toIso8601String() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
</urlset>

