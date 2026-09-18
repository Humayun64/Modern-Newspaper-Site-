{{-- Vertical card: image with badges over it, headline and meta below. --}}
<article class="card">
    <a href="{{ route('post.show', $post->slug) }}" class="thumb">
        <img src="{{ $post->thumb }}" alt="{{ $post->title }}" loading="lazy">
        @include('partials.media-flag')
        @include('partials.thumb-badges')
    </a>

    <h3 class="card-title"><a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a></h3>

    @if (($showExcerpt ?? false) && $post->excerpt)
        <p style="font-size:14px;color:var(--ink-soft);margin:8px 0 0">{{ \Illuminate\Support\Str::limit($post->excerpt, 110) }}</p>
    @endif

    <div class="meta">
        @if ($post->author)<span>&#9679; {{ $post->author->display_name }}</span>@endif
        <span>&#128337; {{ \App\Support\Bangla::ago($post->published_at) }}</span>
    </div>
</article>
