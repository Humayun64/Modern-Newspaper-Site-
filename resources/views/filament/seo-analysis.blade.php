@php
    use App\Support\SeoAnalyzer;

    $score = $report['score'];

    // Three bands, and the wording matters: a writer who sees "poor" on a
    // perfectly good news brief stops reading the panel altogether.
    [$bandLabel, $bandColour] = match (true) {
        $score >= 80 => ['Good', '#16a34a'],
        $score >= 50 => ['Needs work', '#d97706'],
        default      => ['Poor', '#dc2626'],
    };

    $groups = [
        SeoAnalyzer::PROBLEM     => ['Problems', '#dc2626'],
        SeoAnalyzer::IMPROVEMENT => ['Improvements', '#d97706'],
        SeoAnalyzer::GOOD        => ['Good', '#16a34a'],
    ];
@endphp

<div class="seo-panel">
    <style>
        .seo-panel { --line: rgba(128,128,128,.25); font-size: .8125rem; }
        .seo-top { display: flex; align-items: center; gap: .9rem; margin-bottom: .9rem; }
        .seo-score { font-size: 1.5rem; font-weight: 700; line-height: 1; min-width: 3.4rem; }
        .seo-band { font-size: .75rem; font-weight: 600; opacity: .8; }
        .seo-bar { flex: 1; height: 7px; border-radius: 99px; background: rgba(128,128,128,.2); overflow: hidden; }
        .seo-bar i { display: block; height: 100%; border-radius: 99px; transition: width .25s ease; }
        .seo-words { font-size: .7rem; opacity: .6; white-space: nowrap; }

        .seo-group { margin-top: .75rem; }
        .seo-group-head { font-size: .7rem; font-weight: 700; text-transform: uppercase;
                          letter-spacing: .04em; margin-bottom: .35rem; }
        .seo-item { display: flex; gap: .5rem; padding: .35rem 0; border-top: 1px solid var(--line); }
        .seo-item:first-of-type { border-top: 0; }
        .seo-dot { width: .55rem; height: .55rem; border-radius: 50%; flex-shrink: 0; margin-top: .35rem; }
        .seo-label { font-weight: 600; }
        .seo-text { opacity: .78; }
    </style>

    <div class="seo-top">
        <div>
            <div class="seo-score" style="color: {{ $bandColour }}">{{ $score }}%</div>
            <div class="seo-band" style="color: {{ $bandColour }}">{{ $bandLabel }}</div>
        </div>

        <div class="seo-bar">
            <i style="width: {{ $score }}%; background: {{ $bandColour }}"></i>
        </div>

        <div class="seo-words">{{ $report['words'] }} words</div>
    </div>

    @foreach ($groups as $status => [$groupLabel, $groupColour])
        @php $items = array_values(array_filter($report['checks'], fn ($c) => $c['status'] === $status)); @endphp

        @if ($items !== [])
            <div class="seo-group">
                <div class="seo-group-head" style="color: {{ $groupColour }}">
                    {{ $groupLabel }} ({{ count($items) }})
                </div>

                @foreach ($items as $item)
                    <div class="seo-item">
                        <span class="seo-dot" style="background: {{ $groupColour }}"></span>
                        <div>
                            <span class="seo-label">{{ $item['label'] }}:</span>
                            <span class="seo-text">{{ $item['text'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
</div>
