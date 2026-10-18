@php
    use App\Support\Bangla;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Http;

    $s = \App\Models\Setting::all_cached();

    $city = trim((string) ($s['weather_city'] ?? '')) ?: 'ঢাকা';
    $lat  = (float) ($s['weather_lat'] ?? 23.8103);
    $lon  = (float) ($s['weather_lon'] ?? 90.4125);

    /*
     * Open-Meteo: free, no key, no account. Two calls — the forecast and the
     * air quality — cached together as a plain array for half an hour. A
     * cached array cannot come back as a broken object the way a cached
     * model can, and the readings do not move faster than that anyway.
     *
     * Every failure path returns null and the card does not render. On a
     * local machine whose PHP has no CA bundle configured, HTTPS fails and
     * nothing shows — that is a php.ini setting, not a bug here. See the
     * install notes.
     */
    $w = Cache::remember('widget.weather.'.$lat.','.$lon, 1800, function () use ($lat, $lon) {
        try {
            $f = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude'  => $lat,
                'longitude' => $lon,
                'current'   => 'temperature_2m,apparent_temperature,relative_humidity_2m,weather_code,wind_speed_10m',
                'daily'     => 'temperature_2m_max,temperature_2m_min,precipitation_probability_max',
                'timezone'  => 'Asia/Dhaka',
                'forecast_days' => 1,
            ]);

            if ($f->failed()) {
                return null;
            }

            $c = $f->json('current');
            $d = $f->json('daily');

            if (! is_array($c) || ! isset($c['temperature_2m'])) {
                return null;
            }

            // Air quality is a second service and a nice-to-have: if it is
            // slow or down, the card still shows the weather.
            $aqi = null;

            try {
                $a = Http::timeout(4)->get('https://air-quality-api.open-meteo.com/v1/air-quality', [
                    'latitude'  => $lat,
                    'longitude' => $lon,
                    'current'   => 'us_aqi',
                    'timezone'  => 'Asia/Dhaka',
                ]);

                if ($a->successful() && is_numeric($a->json('current.us_aqi'))) {
                    $aqi = (int) round($a->json('current.us_aqi'));
                }
            } catch (\Throwable $e) {
                // leave $aqi null
            }

            return [
                'temp'     => (int) round($c['temperature_2m']),
                'feels'    => isset($c['apparent_temperature']) ? (int) round($c['apparent_temperature']) : null,
                'humidity' => isset($c['relative_humidity_2m']) ? (int) round($c['relative_humidity_2m']) : null,
                'wind'     => isset($c['wind_speed_10m']) ? (int) round($c['wind_speed_10m']) : null,
                'code'     => (int) ($c['weather_code'] ?? 0),
                'max'      => isset($d['temperature_2m_max'][0]) ? (int) round($d['temperature_2m_max'][0]) : null,
                'min'      => isset($d['temperature_2m_min'][0]) ? (int) round($d['temperature_2m_min'][0]) : null,
                'rain'     => isset($d['precipitation_probability_max'][0]) ? (int) $d['precipitation_probability_max'][0] : null,
                'aqi'      => $aqi,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    });

    /** WMO codes, grouped down to what is worth naming in Bangla. */
    $describe = function (int $code): array {
        return match (true) {
            $code === 0                => ['☀', 'পরিষ্কার আকাশ'],
            $code <= 2                 => ['⛅', 'আংশিক মেঘলা'],
            $code === 3                => ['☁', 'মেঘলা'],
            $code <= 48                => ['🌫', 'কুয়াশা'],
            $code <= 57                => ['🌦', 'গুঁড়ি গুঁড়ি বৃষ্টি'],
            $code >= 71 && $code <= 77 => ['🌨', 'তুষারপাত'],
            $code <= 67, $code <= 82   => ['🌧', 'বৃষ্টি'],
            $code <= 86                => ['🌨', 'তুষারপাত'],
            default                    => ['⛈', 'বজ্রসহ বৃষ্টি'],
        };
    };

    /** US AQI bands. The word carries the meaning; the bar only supports it. */
    $aqiBand = function (int $v): string {
        return match (true) {
            $v <= 50  => 'ভালো',
            $v <= 100 => 'সহনীয়',
            $v <= 150 => 'সংবেদনশীলদের জন্য ক্ষতিকর',
            $v <= 200 => 'অস্বাস্থ্যকর',
            $v <= 300 => 'খুবই অস্বাস্থ্যকর',
            default   => 'বিপজ্জনক',
        };
    };
@endphp

@if ($w)
    @php [$icon, $label] = $describe($w['code']); @endphp

    <section class="block weather-card">
        <div class="wx-place">
            <span class="wx-pin" aria-hidden="true">&#9679;</span>{{ $city }}
        </div>

        <div class="wx-main">
            <span class="wx-icon" aria-hidden="true">{{ $icon }}</span>

            <span class="wx-temp">{{ Bangla::digits((string) $w['temp']) }}&deg;</span>

            <div class="wx-side">
                <span class="wx-label">{{ $label }}</span>

                @if ($w['feels'] !== null)
                    <span class="wx-feels">অনুভূত {{ Bangla::digits((string) $w['feels']) }}&deg;</span>
                @endif

                <span class="wx-range">
                    @if ($w['max'] !== null)
                        <span>&uarr;{{ Bangla::digits((string) $w['max']) }}&deg;</span>
                    @endif
                    @if ($w['min'] !== null)
                        <span>&darr;{{ Bangla::digits((string) $w['min']) }}&deg;</span>
                    @endif
                    @if ($w['rain'] !== null)
                        <span>&#9730; {{ Bangla::digits((string) $w['rain']) }}%</span>
                    @endif
                </span>
            </div>
        </div>

        @if ($w['aqi'] !== null)
            @php
                // The scale runs to 500; anything above that is off the chart
                // and pinned to the end rather than drawn outside the bar.
                $pos = max(0, min(100, $w['aqi'] / 500 * 100));
            @endphp

            <div class="wx-aqi">
                <div class="wx-aqi-head">
                    <span class="wx-aqi-title">বায়ুর মান</span>
                    <span class="wx-aqi-value">{{ Bangla::digits((string) $w['aqi']) }}</span>
                </div>

                <div class="wx-aqi-band">{{ $aqiBand($w['aqi']) }}</div>

                <div class="wx-aqi-bar" role="img"
                     aria-label="বায়ুর মান সূচক {{ $w['aqi'] }} — {{ $aqiBand($w['aqi']) }}">
                    <span class="wx-aqi-dot" style="inset-inline-start: {{ round($pos, 1) }}%"></span>
                </div>

                <div class="wx-aqi-scale">
                    <span>০</span><span>১০০</span><span>২০০</span><span>৩০০</span><span>৪০০</span><span>৫০০</span>
                </div>
            </div>
        @endif

        @if ($w['humidity'] !== null || $w['wind'] !== null)
            <dl class="wx-grid">
                @if ($w['humidity'] !== null)
                    <div><dt>আর্দ্রতা</dt><dd>{{ Bangla::digits((string) $w['humidity']) }}%</dd></div>
                @endif

                @if ($w['wind'] !== null)
                    <div><dt>বাতাস</dt><dd>{{ Bangla::digits((string) $w['wind']) }} কিমি/ঘ</dd></div>
                @endif
            </dl>
        @endif
    </section>
@endif
