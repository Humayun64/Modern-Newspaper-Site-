@php $sid = 'slider-'.uniqid(); @endphp

<section class="block">
    {{-- The arrows used to live up here beside the heading. They now sit on
         the picture, where প্রধান সংবাদ keeps them and where a thumb already
         is. The heading row is left with the title alone. --}}
    <div class="block-head">
        <h2 class="block-title">{{ $title ?: 'সংবাদ স্লাইডার' }}</h2>
    </div>

    <div class="slider ps-slider">
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

        {{-- One slide is not a slider, so the arrows stay away. --}}
        @if ($posts->count() > 1)
            <div class="ps-nav">
                <button type="button" class="ps-arrow"
                        data-slider-prev="{{ $sid }}" aria-label="আগের খবর">&lsaquo;</button>
                <button type="button" class="ps-arrow"
                        data-slider-next="{{ $sid }}" aria-label="পরের খবর">&rsaquo;</button>
            </div>
        @endif
    </div>
</section>
