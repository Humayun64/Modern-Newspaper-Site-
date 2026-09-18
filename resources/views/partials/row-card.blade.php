{{-- Small horizontal item: square thumb left, headline right. --}}
<article class="row-card">
    <a href="{{ route('post.show', $post->slug) }}" class="thumb">
        <img src="{{ $post->thumb }}" alt="{{ $post->title }}" loading="lazy">
        @include('partials.media-flag')
    </a>
    <div>
        <h3 class="card-title"><a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a></h3>
        <div class="meta"><span>&#128337; {{ \App\Support\Bangla::ago($post->published_at) }}</span></div>
    </div>
</article>
