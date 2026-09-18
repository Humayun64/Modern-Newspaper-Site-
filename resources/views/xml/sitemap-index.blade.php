{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($maps as $map)
    <sitemap>
        <loc>{{ $map }}</loc>
        <lastmod>{{ $updated->toAtomString() }}</lastmod>
    </sitemap>
@endforeach
</sitemapindex>
