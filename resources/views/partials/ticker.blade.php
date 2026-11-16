@php
    /*
     * The breaking strip at the top of the homepage.
     *
     * Two ways it can be filled, and the first one that has anything wins:
     *
     *   1. the stories ticked as "Breaking news" — on the article screen, or
     *      on the Breaking news page in the admin
     *   2. otherwise, simply the newest published articles
     *
     * The fallback is the point. An editor who marks nothing still gets a
     * strip, and an empty strip at the top of a news homepage reads as a
     * broken site. The moment one story is ticked, the ticks take over
     * completely — no mixing, because a reader cannot tell which of the two
     * a headline came from and an editor would not expect their choice to
     * share the row with whatever happened to be newest.
     *
     * Video stories are left out of both: they have their own page.
     *
     * Cached for a minute, as plain arrays — never Eloquent models. The
     * database cache store serialises what it is handed, and a serialised
     * model comes back as an incomplete object with no methods on it. The
     * admin page clears this key when anything is ticked, so an editor is
     * never left wondering whether it worked.
     */
    $count = (int) (\App\Models\Setting::all_cached()['breaking_count'] ?? 4) ?: 4;

    $items = \Illuminate\Support\Facades\Cache::remember(
        'ticker.chosen.'.$count,
        60,
        function () use ($count) {
            $columns = ['id', 'title', 'slug', 'featured_image', 'type', 'category_id'];

            $rows = \App\Support\VideoContent::exclude(
                    \App\Models\Post::published()->where('is_breaking', true)
                )
                ->latestFirst()
                ->limit($count)
                ->get($columns);

            if ($rows->isEmpty()) {
                $rows = \App\Support\VideoContent::exclude(\App\Models\Post::published())
                    ->latestFirst()
                    ->limit($count)
                    ->get($columns);
            }

            return $rows->map(fn ($p) => [
                'title' => $p->title,
                'url'   => route('post.show', $p->slug),
                'thumb' => $p->thumb,
            ])->all();
        }
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
