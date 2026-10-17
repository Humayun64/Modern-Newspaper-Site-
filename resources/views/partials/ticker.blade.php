@php
    /*
     * Correction 13: a breaking headline stops being breaking after a day.
     *
     * The day runs from the moment an article was MARKED breaking, not from
     * when it was published — every article here was imported weeks ago, so
     * measuring from publish time would mean nothing could ever be breaking.
     *
     * The limit is applied HERE as well as by site:expire-breaking, on
     * purpose. The scheduled command keeps the admin list tidy, but a missed
     * cron run would otherwise leave yesterday's headline scrolling across
     * the top of the site. This query is what readers see, so it enforces
     * the rule itself and does not depend on the cron.
     *
     * Cache plain arrays, never Eloquent models. The database cache store
     * serialises whatever it is given; a serialised model can come back as
     * an incomplete object with no methods on it.
     */
    $breakingHours = (int) (\App\Models\Setting::all_cached()['breaking_hours'] ?? 24) ?: 24;

    $breaking = \Illuminate\Support\Facades\Cache::remember(
        'ticker.breaking.'.$breakingHours,
        60,
        fn () => \App\Models\Post::published()
            ->where('is_breaking', true)
            ->stillBreaking($breakingHours)
            ->orderByDesc('breaking_since')
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
