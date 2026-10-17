@php
    use App\Support\Bangla;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Http;

    $s = \App\Models\Setting::all_cached();

    /*
     * Correction 15: Dhaka weather and the stock index, top right.
     *
     * Weather comes from Open-Meteo — free, no key, no account. The result is
     * cached as a plain array for half an hour: the figure does not change
     * faster than that, and a cached array cannot come back as a broken
     * object the way a cached model can.
     *
     * Every failure path returns null and the widget simply does not render,
     * so a slow or unreachable API never holds up the page or shows an error
     * where a temperature should be.
     */
    $weather = null;

    if (($s['weather_enabled'] ?? '1') !== '0') {
        $weather = Cache::remember('topbar.weather', 1800, function () {
            try {
                $response = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude'  => 23.8103,   // Dhaka
                    'longitude' => 90.4125,
                    'current'   => 'temperature_2m,weather_code',
                    'timezone'  => 'Asia/Dhaka',
                ]);

                if ($response->failed()) {
                    return null;
                }

                $current = $response->json('current');

                if (! is_array($current) || ! isset($current['temperature_2m'])) {
                    return null;
                }

                return [
                    'temp' => (int) round($current['temperature_2m']),
                    'code' => (int) ($current['weather_code'] ?? 0),
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });
    }

    /** WMO weather codes, grouped down to the handful worth drawing. */
    $icon = function (int $code): string {
        return match (true) {
            $code === 0                 => '☀',
            $code <= 3                  => '⛅',
            $code <= 48                 => '🌫',
            $code >= 71 && $code <= 77  => '🌨',
            $code <= 82                 => '🌧',
            default                     => '⛈',
        };
    };

    // The stock figure is typed in by the admin: DSE publishes no free feed,
    // and a scraped page would break the day they change their markup.
    $dseIndex  = trim((string) ($s['dse_index'] ?? ''));
    $dseChange = trim((string) ($s['dse_change'] ?? ''));

    // "+1.24" rises, "-0.82" falls. Anything else is shown without a colour.
    $direction = match (true) {
        str_starts_with($dseChange, '-') => 'down',
        $dseChange !== ''                => 'up',
        default                          => null,
    };
@endphp

@if ($weather || $dseIndex !== '')
    <div class="topbar-widgets">
        @if ($weather)
            <span class="tw tw-weather" title="ঢাকার আবহাওয়া">
                <span class="tw-icon" aria-hidden="true">{{ $icon($weather['code']) }}</span>
                <span class="tw-label">ঢাকা</span>
                <strong>{{ Bangla::digits((string) $weather['temp']) }}&deg;</strong>
            </span>
        @endif

        @if ($dseIndex !== '')
            <span class="tw tw-stock" title="ঢাকা স্টক এক্সচেঞ্জ">
                <span class="tw-label">DSEX</span>
                <strong>{{ Bangla::digits($dseIndex) }}</strong>

                @if ($direction)
                    <span class="tw-change is-{{ $direction }}">
                        {{ $direction === 'up' ? '▲' : '▼' }}{{ Bangla::digits(ltrim($dseChange, '+-')) }}%
                    </span>
                @endif
            </span>
        @endif
    </div>
@endif
