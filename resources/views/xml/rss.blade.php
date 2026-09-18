{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0"
     xmlns:atom="http://www.w3.org/2005/Atom"
     xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ $title }}</title>
        <link>{{ $link }}</link>
        <description>{{ $description }}</description>
        <language>bn</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ $self }}" rel="self" type="application/rss+xml" />

@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('post.show', $post->slug) }}</link>
            <guid isPermaLink="true">{{ route('post.show', $post->slug) }}</guid>
            <pubDate>{{ $post->published_at?->toRssString() }}</pubDate>
            @if ($post->category)<category>{{ $post->category->name }}</category>@endif
            @if ($post->author)<dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">{{ $post->author->display_name }}</dc:creator>@endif
            <description>{{ $post->excerpt }}</description>
            @if ($post->featured_image)
            <enclosure url="{{ $post->thumb }}" type="image/jpeg" length="0" />
            @endif
        </item>
@endforeach
    </channel>
</rss>
