{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">
@foreach ($posts as $post)
    <url>
        <loc>{{ route('post.show', $post->slug) }}</loc>
        <news:news>
            <news:publication>
                <news:name>{{ $siteName }}</news:name>
                <news:language>bn</news:language>
            </news:publication>
            <news:publication_date>{{ $post->published_at?->toAtomString() }}</news:publication_date>
            <news:title>{{ $post->title }}</news:title>
        </news:news>
    </url>
@endforeach
</urlset>
