@php
    /*
     * Cache plain arrays, never Eloquent models.
     * The database cache store serialises whatever it is given; a serialised
     * model can come back as an incomplete object with no methods on it.
     */
    $breaking = \Illuminate\Support\Facades\Cache::remember('ticker.breaking', 60, fn () =>
        \App\Models\Post::published()
            ->where('is_breaking', true)
            ->latestFirst()
            ->limit(8)
            ->get(['id', 'title', 'slug', 'featured_image'])
            ->map(fn ($p) => [
                'title' => $p->title,
                'url'   => route('post.show', $p->slug),
                'thumb' => $p->thumb,
            ])
            ->all()
    );
@endphp

@if (! empty($breaking))
    <div class="wrap">
        <div class="ticker">
            <div class="ticker-label">ব্রেকিং</div>

            {{-- The viewport clips the moving track. Without it the scrolling
                 headlines slide straight over the red label. --}}
            <div class="ticker-viewport">
                <div class="ticker-track">
                    @foreach ([false, true] as $isClone)
                        @foreach ($breaking as $item)
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
