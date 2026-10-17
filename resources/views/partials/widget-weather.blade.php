@php
    use App\Support\Bangla;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Http;

    $s = \App\Models\Setting::all_cached();

    $city = trim((string) ($s['weather_city'] ?? '')) ?: 'ঢাকা';
    $lat  = (float) ($s['weather_lat'] ?? 23.8103);
    $lon  = (float) ($s['weather_lon'] ?? 90.4125);

    /*
     * Open-Meteo: free, no key, no account. Cached as a plain array for half
     * an hour — the reading does not move faster than that, and a cached
     * array cannot come back as a broken object the way a cached model can.
     *
     * Every failure path returns null and the card simply does not render,
     * so a slow or unreachable API never holds up the page.
     */
    $w = Cache::remember('widget.weather.'.$lat.','.$lon, 1800, function () use ($lat, $lon) {
        try {
            $response = Http::timeout(4)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude'  => $lat,
                'longitude' => $lon,
                'current'   => 'temperature_2m,apparent_temperature,relative_humidity_2m,weather_code,wind_speed_10m',
                'daily'     => 'temperature_2m_max,temperature_2m_min',
                'timezone'  => 'Asia/Dhaka',
                'forecast_days' => 1,
            ]);

            if ($response->failed()) {
                return null;
            }

            $c = $response->json('current');
            $d = $response->json('daily');

            if (! is_array($c) || ! isset($c['temperature_2m'])) {
                return null;
            }

            return [
                'temp'     => (int) round($c['temperature_2m']),
                'feels'    => isset($c['apparent_temperature']) ? (int) round($c['apparent_temperature']) : null,
                'humidity' => isset($c['relative_humidity_2m']) ? (int) round($c['relative_humidity_2m']) : null,
                'wind'     => isset($c['wind_speed_10m']) ? (int) round($c['wind_speed_10m']) : null,
                'code'     => (int) ($c['weather_code'] ?? 0),
                'max'      => isset($d['temperature_2m_max'][0]) ? (int) round($d['temperature_2m_max'][0]) : null,
                'min'      => isset($d['temperature_2m_min'][0]) ? (int) round($d['temperature_2m_min'][0]) : null,
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
            $code <= 67                => ['🌧', 'বৃষ্টি'],
            $code <= 82                => ['🌧', 'বৃষ্টি'],
            $code <= 86                => ['🌨', 'তুষারপাত'],
            default                    => ['⛈', 'বজ্রসহ বৃষ্টি'],
        };
    };
@endphp

@if ($w)
    @php [$icon, $label] = $describe($w['code']); @endphp

    <section class="block weather-card">
        <div class="block-head">
            <h2 class="block-title">আবহাওয়া</h2>
            <span class="block-more">{{ $city }}</span>
        </div>

        <div class="wx-main">
            <span class="wx-icon" aria-hidden="true">{{ $icon }}</span>

            <div class="wx-now">
                <span class="wx-temp">{{ Bangla::digits((string) $w['temp']) }}&deg;</span>
                <span class="wx-label">{{ $label }}</span>
            </div>
        </div>

        <dl class="wx-grid">
            @if ($w['feels'] !== null)
                <div><dt>অনুভূত</dt><dd>{{ Bangla::digits((string) $w['feels']) }}&deg;</dd></div>
            @endif

            @if ($w['max'] !== null && $w['min'] !== null)
                <div>
                    <dt>সর্বোচ্চ / সর্বনিম্ন</dt>
                    <dd>{{ Bangla::digits((string) $w['max']) }}&deg; / {{ Bangla::digits((string) $w['min']) }}&deg;</dd>
                </div>
            @endif

            @if ($w['humidity'] !== null)
                <div><dt>আর্দ্রতা</dt><dd>{{ Bangla::digits((string) $w['humidity']) }}%</dd></div>
            @endif

            @if ($w['wind'] !== null)
                <div><dt>বাতাস</dt><dd>{{ Bangla::digits((string) $w['wind']) }} কিমি/ঘ</dd></div>
            @endif
        </dl>
    </section>
@endif
