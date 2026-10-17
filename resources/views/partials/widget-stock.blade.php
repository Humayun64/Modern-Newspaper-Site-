@php
    use App\Support\Bangla;

    $s = \App\Models\Setting::all_cached();

    /*
     * Two ways to fill this card, and the embed wins when it is there.
     *
     * 1. An embed from a market-data provider (TradingView and the like give
     *    away a widget for this). That is what sites showing a live DSE chart
     *    are actually running — the exchange publishes no free feed of its
     *    own, so there is nothing to build against directly.
     *
     * 2. Failing that, numbers typed into Site settings. Less impressive,
     *    always correct, and no third-party script on the page.
     *
     * The embed is printed unescaped. Only an admin can set it, and it is the
     * same trust you give any "paste this code" widget — but it is the reason
     * this field is not exposed to editors.
     */
    $embed  = trim((string) ($s['stock_embed'] ?? ''));
    $index  = trim((string) ($s['dse_index'] ?? ''));
    $change = trim((string) ($s['dse_change'] ?? ''));
    $asOf   = trim((string) ($s['dse_as_of'] ?? ''));

    $direction = match (true) {
        str_starts_with($change, '-') => 'down',
        $change !== ''                => 'up',
        default                       => null,
    };
@endphp

@if ($embed !== '' || $index !== '')
    <section class="block stock-card">
        <div class="block-head">
            <h2 class="block-title">ঢাকা স্টক এক্সচেঞ্জ</h2>
            @if ($asOf !== '')
                <span class="block-more">{{ $asOf }}</span>
            @endif
        </div>

        @if ($embed !== '')
            <div class="stock-embed">{!! $embed !!}</div>
        @else
            <div class="stock-figure">
                <span class="stock-name">DSEX</span>

                <span class="stock-value">{{ Bangla::digits($index) }}</span>

                @if ($direction)
                    <span class="stock-change is-{{ $direction }}">
                        {{ $direction === 'up' ? '▲' : '▼' }} {{ Bangla::digits(ltrim($change, '+-')) }}%
                    </span>
                @endif
            </div>

            <p class="stock-note">সূচক প্রতিদিন হালনাগাদ করা হয়।</p>
        @endif

        <a class="stock-link" href="https://www.dsebd.org/" target="_blank" rel="noopener">
            ডিএসই ওয়েবসাইট
        </a>
    </section>
@endif
