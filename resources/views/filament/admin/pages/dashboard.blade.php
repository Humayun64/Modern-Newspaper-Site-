<x-filament-panels::page>

<style>
/* Scoped to .nr so nothing here leaks into the rest of the panel. */
.nr {
    --surface:   #fcfcfb;
    --plane:     #f9f9f7;
    --ink:       #0b0b0b;
    --ink-2:     #52514e;
    --muted:     #898781;
    --grid:      #e1e0d9;
    --baseline:  #c3c2b7;
    --ring:      rgba(11,11,11,.10);
    --series:    #2a78d6;     /* single-series hue — sequential blue */
    --good:      #0ca30c;
    --warning:   #fab219;
    --serious:   #ec835a;
    --critical:  #d03b3b;
}
@media (prefers-color-scheme: dark) {
    .nr {
        --surface: #1a1a19; --plane: #0d0d0d; --ink: #ffffff; --ink-2: #c3c2b7;
        --muted: #898781; --grid: #2c2c2a; --baseline: #383835;
        --ring: rgba(255,255,255,.10); --series: #3987e5;
    }
}
.dark .nr {
    --surface: #1a1a19; --plane: #0d0d0d; --ink: #ffffff; --ink-2: #c3c2b7;
    --muted: #898781; --grid: #2c2c2a; --baseline: #383835;
    --ring: rgba(255,255,255,.10); --series: #3987e5;
}
html:not(.dark) .nr {
    --surface: #fcfcfb; --plane: #f9f9f7; --ink: #0b0b0b; --ink-2: #52514e;
    --grid: #e1e0d9; --baseline: #c3c2b7; --ring: rgba(11,11,11,.10); --series: #2a78d6;
}

.nr { display: grid; gap: 1rem; color: var(--ink);
      font-family: system-ui, -apple-system, "Segoe UI", sans-serif; }

.nr-card {
    background: var(--surface); border: 1px solid var(--ring);
    border-radius: .75rem; padding: 1.1rem 1.25rem;
}
.nr-card-title {
    font-size: .8125rem; font-weight: 600; color: var(--ink-2);
    margin: 0 0 .9rem; letter-spacing: .01em;
}
.nr-sub { font-size: .75rem; color: var(--muted); font-weight: 400; }

/* ---- stat tiles ---- */
.nr-tiles { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); }
.nr-tile-label { font-size: .75rem; color: var(--muted); margin: 0 0 .3rem; }
.nr-tile-value { font-size: 2rem; font-weight: 700; line-height: 1.1; margin: 0; }
.nr-tile-note { font-size: .75rem; color: var(--ink-2); margin: .35rem 0 0; }
.nr-up   { color: var(--good); font-weight: 600; }
.nr-flat { color: var(--muted); }

/* ---- two-column rows ---- */
.nr-split { display: grid; gap: 1rem; grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr); }
.nr-split-even { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }

/* ---- bar chart ---- */
.nr-chart { display: grid; gap: .5rem; }
.nr-bars {
    display: flex; align-items: flex-end; gap: 4px;
    height: 150px; padding-bottom: 2px;
    border-bottom: 1px solid var(--baseline);
}
.nr-col { flex: 1; position: relative; display: flex; flex-direction: column; justify-content: flex-end; height: 100%; }
.nr-bar {
    height: var(--h); min-height: 2px;
    background: var(--series); border-radius: 4px 4px 0 0;
}
.nr-col[data-zero="1"] .nr-bar { background: var(--grid); }
.nr-tip {
    position: absolute; bottom: calc(var(--h) + 8px); left: 50%; transform: translateX(-50%);
    background: var(--ink); color: var(--surface);
    font-size: .6875rem; white-space: nowrap; padding: .25rem .5rem;
    border-radius: .3rem; opacity: 0; pointer-events: none; transition: opacity .12s; z-index: 5;
}
.nr-col:hover .nr-tip, .nr-col:focus-within .nr-tip { opacity: 1; }
.nr-peak {
    position: absolute; bottom: calc(var(--h) + 4px); left: 50%; transform: translateX(-50%);
    font-size: .6875rem; font-weight: 700; color: var(--ink-2);
}
.nr-xaxis { display: flex; gap: 4px; }
.nr-xaxis span {
    flex: 1; text-align: center; font-size: .6875rem; color: var(--muted);
    font-variant-numeric: tabular-nums;
}

/* ---- attention list ---- */
.nr-list { display: grid; gap: .75rem; }
.nr-att { display: grid; grid-template-columns: 26px minmax(0,1fr) auto; gap: .65rem; align-items: start; }
.nr-ico {
    width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center;
    font-size: .8125rem; font-weight: 700; color: #fff;
}
.ico-good { background: var(--good); } .ico-warning { background: var(--warning); color: #0b0b0b; }
.ico-serious { background: var(--serious); color: #0b0b0b; } .ico-critical { background: var(--critical); }
.nr-att-label { font-size: .875rem; font-weight: 600; margin: 0; }
.nr-att-hint { font-size: .75rem; color: var(--muted); margin: .15rem 0 0; }
.nr-att-count { font-size: 1.0625rem; font-weight: 700; font-variant-numeric: tabular-nums; }

/* ---- ranked rows ---- */
.nr-rows { display: grid; }
.nr-row {
    display: grid; grid-template-columns: 20px minmax(0,1fr) auto; gap: .7rem; align-items: baseline;
    padding: .6rem 0; border-bottom: 1px solid var(--grid);
}
.nr-row:last-child { border-bottom: 0; padding-bottom: 0; }
.nr-rank { font-size: .75rem; font-weight: 700; color: var(--muted); font-variant-numeric: tabular-nums; }
.nr-row-title { font-size: .875rem; font-weight: 600; margin: 0; line-height: 1.45; }
.nr-row-meta { font-size: .75rem; color: var(--muted); margin: .15rem 0 0; }
.nr-num { font-size: .8125rem; font-weight: 700; font-variant-numeric: tabular-nums; white-space: nowrap; }

/* ---- horizontal bars ---- */
.nr-hbars { display: grid; gap: .7rem; }
.nr-hrow { display: grid; gap: .3rem; }
.nr-hhead { display: flex; justify-content: space-between; font-size: .8125rem; }
.nr-hname { font-weight: 600; }
.nr-hcount { color: var(--ink-2); font-variant-numeric: tabular-nums; }
.nr-htrack { height: 8px; background: var(--grid); border-radius: 4px; overflow: hidden; }
.nr-hfill { height: 100%; width: var(--w); background: var(--series); border-radius: 4px; min-width: 4px; }

.nr-empty { font-size: .8125rem; color: var(--muted); margin: 0; }
.nr a { color: inherit; text-decoration: none; }
.nr a:hover { text-decoration: underline; }

@media (max-width: 900px) { .nr-split { grid-template-columns: 1fr; } }
</style>

<div class="nr">

    {{-- ---------------- headline numbers ---------------- --}}
    <div class="nr-tiles">
        <div class="nr-card">
            <p class="nr-tile-label">Published today</p>
            <p class="nr-tile-value">{{ number_format($counts['today']) }}</p>
            <p class="nr-tile-note">
                {{ number_format($counts['week']) }} in the last 7 days
                @if ($counts['week_delta'] > 0)
                    <span class="nr-up">+{{ $counts['week_delta'] }} vs previous</span>
                @elseif ($counts['week_delta'] === 0)
                    <span class="nr-flat">level with previous</span>
                @else
                    <span class="nr-flat">{{ $counts['week_delta'] }} vs previous</span>
                @endif
            </p>
        </div>

        <div class="nr-card">
            <p class="nr-tile-label">In the queue</p>
            <p class="nr-tile-value">{{ number_format($counts['drafts'] + $counts['pending']) }}</p>
            <p class="nr-tile-note">
                {{ number_format($counts['drafts']) }} drafts · {{ number_format($counts['pending']) }} awaiting review
            </p>
        </div>

        <div class="nr-card">
            <p class="nr-tile-label">Scheduled</p>
            <p class="nr-tile-value">{{ number_format($counts['scheduled']) }}</p>
            <p class="nr-tile-note">Going live automatically</p>
        </div>

        <div class="nr-card">
            <p class="nr-tile-label">Total reads</p>
            <p class="nr-tile-value">{{ number_format($counts['reads']) }}</p>
            <p class="nr-tile-note">Across {{ number_format($counts['total']) }} published articles</p>
        </div>
    </div>

    {{-- ---------------- activity + attention ---------------- --}}
    <div class="nr-split">

        <section class="nr-card">
            <p class="nr-card-title">
                Articles published per day
                <span class="nr-sub">— last 14 days, {{ $activity['sum'] }} in total</span>
            </p>

            <div class="nr-chart">
                <div class="nr-bars">
                    @foreach ($activity['days'] as $day)
                        @php $pct = round($day['count'] / $activity['max'] * 100); @endphp
                        <div class="nr-col" style="--h: {{ $day['count'] ? max($pct, 4) : 0 }}%"
                             data-zero="{{ $day['count'] ? '0' : '1' }}" tabindex="0">
                            <span class="nr-tip">{{ $day['date'] }} — {{ $day['count'] }} {{ \Illuminate\Support\Str::plural('article', $day['count']) }}</span>
                            @if ($day['count'] === $activity['max'] && $day['count'] > 0)
                                <span class="nr-peak">{{ $day['count'] }}</span>
                            @endif
                            <div class="nr-bar"></div>
                        </div>
                    @endforeach
                </div>
                <div class="nr-xaxis">
                    @foreach ($activity['days'] as $i => $day)
                        <span>{{ $i % 2 === 0 ? $day['short'] : '' }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="nr-card">
            <p class="nr-card-title">Needs attention</p>

            @if (empty($attention))
                <p class="nr-empty">Nothing waiting. Every published article has an image and a meta description.</p>
            @else
                <div class="nr-list">
                    @foreach ($attention as $item)
                        <div class="nr-att">
                            <span class="nr-ico ico-{{ $item['status'] }}" aria-hidden="true">{{ $item['icon'] }}</span>
                            <div>
                                <p class="nr-att-label">{{ $item['label'] }}</p>
                                <p class="nr-att-hint">{{ $item['hint'] }}</p>
                            </div>
                            <span class="nr-att-count">{{ number_format($item['count']) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- ---------------- top stories + category mix ---------------- --}}
    <div class="nr-split-even">

        <section class="nr-card">
            <p class="nr-card-title">Most read <span class="nr-sub">— last 30 days</span></p>

            @if ($top->isEmpty())
                <p class="nr-empty">No published articles in the last 30 days yet.</p>
            @else
                <div class="nr-rows">
                    @foreach ($top as $i => $item)
                        <div class="nr-row">
                            <span class="nr-rank">{{ $i + 1 }}</span>
                            <div>
                                <p class="nr-row-title">
                                    <a href="{{ route('post.show', $item->slug) }}" target="_blank" rel="noopener">{{ $item->title }}</a>
                                </p>
                                <p class="nr-row-meta">{{ $item->category?->name ?? 'Uncategorised' }}</p>
                            </div>
                            <span class="nr-num">{{ number_format($item->views) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="nr-card">
            <p class="nr-card-title">Coverage by section <span class="nr-sub">— last 30 days</span></p>

            @if ($byCat['rows']->isEmpty())
                <p class="nr-empty">No categories yet.</p>
            @else
                <div class="nr-hbars">
                    @foreach ($byCat['rows'] as $cat)
                        <div class="nr-hrow">
                            <div class="nr-hhead">
                                <span class="nr-hname">{{ $cat->name }}</span>
                                <span class="nr-hcount">{{ number_format($cat->posts_count) }}</span>
                            </div>
                            <div class="nr-htrack">
                                <div class="nr-hfill" style="--w: {{ $cat->posts_count ? max(round($cat->posts_count / $byCat['max'] * 100), 2) : 0 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- ---------------- recently published ---------------- --}}
    <section class="nr-card">
        <p class="nr-card-title">Recently published</p>

        @if ($recent->isEmpty())
            <p class="nr-empty">Nothing published yet.</p>
        @else
            <div class="nr-rows">
                @foreach ($recent as $item)
                    <div class="nr-row">
                        <span class="nr-rank">&bull;</span>
                        <div>
                            <p class="nr-row-title">
                                <a href="{{ route('post.show', $item->slug) }}" target="_blank" rel="noopener">{{ $item->title }}</a>
                            </p>
                            <p class="nr-row-meta">
                                {{ $item->category?->name ?? 'Uncategorised' }}
                                @if ($item->author) · {{ $item->author->display_name }} @endif
                                · {{ $item->published_at?->diffForHumans() }}
                            </p>
                        </div>
                        <span class="nr-num">{{ number_format($item->views) }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>

</x-filament-panels::page>
