{{-- Image card with the headline sitting over the photo. --}}
<article class="overlay-card">
    <a href="{{ route('post.show', $post->slug) }}" class="thumb">
        <img src="{{ $post->thumb }}" alt="{{ $post->title }}" loading="lazy">
        @include('partials.media-flag')
    </a>
    <div class="overlay-body">
        @if ($post->category)
            <span class="badge">{{ $post->category->name }}</span>
        @endif
        <h3 class="card-title"><a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a></h3>
        <div class="meta">
            @if ($post->author)<span>{{ $post->author->display_name }}</span>@endif
            <span>{{ \App\Support\Bangla::ago($post->published_at) }}</span>
        </div>
    </div>
</article>
