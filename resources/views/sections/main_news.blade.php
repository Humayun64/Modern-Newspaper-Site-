@php
    /*
     * প্রধান সংবাদ — a carousel of the top stories, with the rest listed
     * underneath.
     *
     * It reuses the site's existing slider rather than introducing a second
     * one: the same .slider-track markup, the same data-slider-prev/next
     * buttons, and therefore the same autoplay that already runs (six
     * seconds, stops for good the moment a reader touches it, stays still
     * for reduced-motion and while the tab is hidden). A second carousel
     * with its own script would be two things to fix every time.
     *
     * The arrows sit ON the picture here, not in the heading — that is what
     * was asked for, and it is also where a reader's hand already is.
     *
     * How many stories ride the carousel: the setting main_slides, three by
     * default. Everything the section holds beyond that is listed below, so
     * no story appears twice.
     */
    $slideCount = (int) (\App\Models\Setting::all_cached()['main_slides'] ?? 3) ?: 3;

    $slides = $posts->take($slideCount);
    $rest   = $posts->skip($slideCount);

    $sid = 'mn-'.uniqid();
@endphp

<section class="block block-main">
    @include('partials.block-head', ['title' => $title ?: 'প্রধান সংবাদ', 'category' => $category])

    @if ($slides->isNotEmpty())
        <div class="slider mn-slider">
            <div class="slider-track" id="{{ $sid }}" tabindex="0" aria-label="প্রধান সংবাদ">
                @foreach ($slides as $post)
                    <article class="slide">
                        <a href="{{ route('post.show', $post->slug) }}" class="thumb">
                            <img src="{{ $post->thumb }}" alt="{{ $post->title }}"
                                 @if (! $loop->first) loading="lazy" @endif>
                            @include('partials.media-flag')
                        </a>
                        <div class="slide-body">
                            @if ($post->category)
                                <span class="badge">{{ $post->category->name }}</span>
                            @endif
                            <h3 class="slide-title">
                                <a href="{{ route('post.show', $post->slug) }}">{{ $post->title }}</a>
                            </h3>
                            <div class="meta">
                                @if ($post->author)<span>{{ $post->author->display_name }}</span>@endif
                                <span>{{ \App\Support\Bangla::ago($post->published_at) }}</span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- One story is not a carousel, so the arrows stay away. --}}
            @if ($slides->count() > 1)
                <div class="mn-nav">
                    <button type="button" class="mn-arrow"
                            data-slider-prev="{{ $sid }}" aria-label="আগের খবর">&lsaquo;</button>
                    <button type="button" class="mn-arrow"
                            data-slider-next="{{ $sid }}" aria-label="পরের খবর">&rsaquo;</button>
                </div>
            @endif
        </div>
    @endif

    @if ($rest->isNotEmpty())
        <div class="mn-rest">
            @foreach ($rest as $post)
                @include('partials.row-card', ['post' => $post])
            @endforeach
        </div>
    @endif
</section>
