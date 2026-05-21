@php
    echo '<?xml version="1.0" encoding="UTF-8"?>';
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    @foreach($urls as $url)
        <url>
            <loc>{{ $url }}</loc>
            <lastmod>{{ now()->toAtomString() }}</lastmod>
            <changefreq>weekly</changefreq>
            <priority>1</priority>
        </url>
    @endforeach
</urlset>
