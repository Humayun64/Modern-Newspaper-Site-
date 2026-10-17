@php
    /*
     * Correction 20: "বিশ্ব গণমাধ্যমে" — foreign coverage, each with a PDF.
     *
     * Plain arrays in the cache, never models: the database cache store
     * serialises what it is handed, and a serialised model comes back as an
     * incomplete object. Five minutes is enough — an editor adding one wants
     * to see it on the site without waiting, and the admin screen clears
     * this key anyway.
     */
    $coverage = \Illuminate\Support\Facades\Cache::remember('home.global_coverage', 300, fn () =>
        \App\Models\GlobalCoverage::active()
            ->limit(6)
            ->get()
            ->map(fn ($c) => [
                'title'   => $c->title,
                'outlet'  => $c->outlet,
                'summary' => $c->summary,
                'link'    => $c->link,
                'cover'   => $c->cover_url,
                'is_pdf'  => $c->has_pdf,
                'date'    => $c->published_on?->format('Y-m-d'),
            ])
            ->all()
    );
@endphp

@if (! empty($coverage))
    <section class="block">
        <div class="block-head">
            <h2 class="block-title">বিশ্ব গণমাধ্যমে</h2>
            <span class="block-more">আন্তর্জাতিক সংবাদমাধ্যমে বাংলাদেশ</span>
        </div>

        <div class="gc-grid">
            @foreach ($coverage as $item)
                @php $tag = $item['link'] ? 'a' : 'div'; @endphp

                <{{ $tag }} class="gc-card"
                    @if ($item['link']) href="{{ $item['link'] }}" target="_blank" rel="noopener" @endif>

                    <div class="gc-card-cover">
                        @if ($item['cover'])
                            <img src="{{ $item['cover'] }}" alt="" loading="lazy">
                        @else
                            {{-- No thumbnail: the outlet's initials stand in, which
                                 reads better than an empty grey box. --}}
                            <span class="gc-card-initials">
                                {{ \Illuminate\Support\Str::of($item['outlet'] ?: $item['title'])->substr(0, 2)->upper() }}
                            </span>
                        @endif

                        @if ($item['is_pdf'])
                            <span class="gc-card-pdf">PDF</span>
                        @endif
                    </div>

                    <div class="gc-card-body">
                        @if ($item['outlet'])
                            <span class="gc-card-outlet">{{ $item['outlet'] }}</span>
                        @endif

                        <h3 class="gc-card-title">{{ $item['title'] }}</h3>

                        @if ($item['summary'])
                            <p class="gc-card-summary">{{ $item['summary'] }}</p>
                        @endif

                        @if ($item['date'])
                            <span class="gc-card-date">
                                {{ \App\Support\Bangla::date(\Carbon\Carbon::parse($item['date'])) }}
                            </span>
                        @endif
                    </div>
                </{{ $tag }}>
            @endforeach
        </div>
    </section>
@endif
