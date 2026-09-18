@php $sid = 'slider-'.uniqid(); @endphp

<section class="block">
    <div class="block-head">
        <h2 class="block-title">{{ $title ?: 'সংবাদ স্লাইডার' }}</h2>
        <div class="slider-nav">
            <button type="button" data-slider-prev="{{ $sid }}" aria-label="Previous">&lsaquo;</button>
            <button type="button" data-slider-next="{{ $sid }}" aria-label="Next">&rsaquo;</button>
        </div>
    </div>

    <div class="slider">
        <div class="slider-track" id="{{ $sid }}" tabindex="0" aria-label="স্লাইডার">
            @foreach ($posts as $post)
                <article class="slide">
                    <a href="{{ route('post.show', $post->slug) }}" class="thumb">
                        <img src="{{ $post->thumb }}" alt="{{ $post->title }}" loading="lazy">
                        @include('partials.media-flag')
                    </a>
                    <div class="slide-body">
                        @if ($post->category)
                            <span class="badge">{{ $post->category->name }}</span>
                        @endif
                        <h3 class="slide-title"><a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a></h3>
                        <div class="meta">
                            @if ($post->author)<span>{{ $post->author->display_name }}</span>@endif
                            <span>{{ \App\Support\Bangla::ago($post->published_at) }}</span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
