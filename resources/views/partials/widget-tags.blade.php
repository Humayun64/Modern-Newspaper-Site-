@php
    /*
     * Corrections 11 and 12: the tag chips were dead ends.
     *
     * The old query ordered by a stored posts_count column and took the top
     * twelve whatever they were. After the WordPress import that column was
     * never recounted, so tags with nothing behind them were being shown —
     * click one and the page says there is no news in it. Which is what
     * "ট্যাগ কাজ করে না" means: the tag works, it is just empty.
     *
     * Now the count is done live, against published articles only, and a tag
     * with none simply does not appear. The chips are therefore always
     * clickable to somewhere real.
     *
     * Cached as plain arrays for ten minutes — this is three joins on every
     * page otherwise, and the answer barely moves.
     */
    $tags = \Illuminate\Support\Facades\Cache::remember('widget.tags', 600, function () {
        return \App\Models\Tag::query()
            ->withCount(['posts as live_count' => fn ($q) => $q->published()])
            ->having('live_count', '>', 0)
            ->orderByDesc('live_count')
            ->limit(12)
            ->get()
            ->map(fn ($t) => [
                'name'  => $t->name,
                'url'   => route('tag.show', $t->slug),
                'count' => $t->live_count,
            ])
            ->all();
    });
@endphp

@if (! empty($tags))
    <section class="block">
        @include('partials.block-head', ['title' => 'জনপ্রিয় ট্যাগ', 'category' => null])

        <div class="chip-row">
            @foreach ($tags as $tag)
                {{-- The count is used to pick and order the tags, but not
                     shown: "বাংলাদেশ ৩" reads like part of the tag's name. --}}
                <a class="chip" href="{{ $tag['url'] }}">{{ $tag['name'] }}</a>
            @endforeach
        </div>
    </section>
@endif
