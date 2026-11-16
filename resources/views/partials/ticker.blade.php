@php
    /*
     * The breaking strip at the top of the homepage.
     *
     * It is simply THE FOUR NEWEST PUBLISHED ARTICLES. Always on, nothing
     * to tick, nothing that expires. Publish a new article and it enters at
     * the front; the oldest of the four drops out on its own.
     *
     * The "Breaking news" tick on the post screen no longer decides what
     * appears here. It used to, together with a 24-hour window, and the
     * result was a strip that vanished for a fortnight because nobody
     * ticked anything. A strip the client has to feed by hand is a strip
     * that is empty most of the time.
     *
     * How many it shows: the setting breaking_count, four by default.
     *
     * Cached for a minute, as plain arrays — never Eloquent models. The
     * database cache store serialises what it is handed, and a serialised
     * model comes back as an incomplete object with no methods on it. One
     * minute also means a newly published article reaches the strip almost
     * at once, which is the point of it.
     */
    $count = (int) (\App\Models\Setting::all_cached()['breaking_count'] ?? 4) ?: 4;

    $items = \Illuminate\Support\Facades\Cache::remember(
        'ticker.latest.'.$count,
        60,
        fn () => \App\Models\Post::published()
            ->latestFirst()
            ->limit($count)
            ->get(['id', 'title', 'slug', 'featured_image'])
            ->map(fn ($p) => [
                'title' => $p->title,
                'url'   => route('post.show', $p->slug),
                'thumb' => $p->thumb,
            ])
            ->all()
    );
@endphp

@if (! empty($items))
    <div class="wrap">
        <div class="ticker">
            <div class="ticker-label">ব্রেকিং</div>

            {{-- The viewport clips the moving track. Without it the scrolling
                 headlines slide straight over the red label. --}}
            <div class="ticker-viewport">
                <div class="ticker-track">
                    @foreach ([false, true] as $isClone)
                        @foreach ($items as $item)
                            <a class="ticker-item" href="{{ $item['url'] }}"
                               @if($isClone) aria-hidden="true" tabindex="-1" @endif>
                                <img src="{{ $item['thumb'] }}" alt="" loading="lazy">
                                <span>{{ $item['title'] }}</span>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif
