@php
    use App\Support\Bangla;
    use Illuminate\Support\Facades\Cache;
    use Illuminate\Support\Facades\Http;

    $s = \App\Models\Setting::all_cached();

    $enabled = ($s['prayer_enabled'] ?? '1') === '1';
    $city    = trim((string) ($s['prayer_city'] ?? '')) ?: (trim((string) ($s['weather_city'] ?? '')) ?: 'ঢাকা');
    $lat     = (float) ($s['prayer_lat'] ?? $s['weather_lat'] ?? 23.8103);
    $lon     = (float) ($s['prayer_lon'] ?? $s['weather_lon'] ?? 90.4125);
    // 1 = University of Islamic Sciences, Karachi — the calculation most
    // widely used in Bangladesh. Changeable in Site settings.
    // A blank setting casts to 0, which is a different calculation
    // altogether — so an empty box means "the default", not "method zero".
    $method  = (int) (trim((string) ($s['prayer_method'] ?? '')) ?: 1);

    /*
     * Times come from AlAdhan — free, no key, no account.
     *
     * Cached under today's date, so the first reader of the day fetches them
     * and everyone after that reads the cache; the key changes by itself at
     * midnight. Plain arrays, never models, for the same reason as elsewhere.
     *
     * A failure returns null and the card does not render.
     */
    $times = Cache::remember(
        'widget.prayer.'.now()->toDateString().'.'.$lat.','.$lon.'.'.$method,
        60 * 60 * 12,
        function () use ($lat, $lon, $method) {
            try {
                // The date is part of the PATH, not a query parameter.
                // Calling /v1/timings bare is a 400 from them — which is
                // exactly what the card was quietly swallowing.
                $r = Http::timeout(5)->get('https://api.aladhan.com/v1/timings/'.now()->format('d-m-Y'), [
                    'latitude'  => $lat,
                    'longitude' => $lon,
                    'method'    => $method,
                    'school'    => 1,       // Hanafi, for Asr
                    'timezonestring' => 'Asia/Dhaka',
                ]);

                if ($r->failed()) {
                    return null;
                }

                $t = $r->json('data.timings');

                if (! is_array($t) || ! isset($t['Fajr'])) {
                    return null;
                }

                // Keep only what is shown, and strip the " (+06)" AlAdhan
                // sometimes appends to a time.
                $keep = ['Fajr', 'Sunrise', 'Dhuhr', 'Asr', 'Maghrib', 'Isha'];
                $out  = [];

                foreach ($keep as $k) {
                    if (! isset($t[$k])) {
                        continue;
                    }

                    $out[$k] = trim(explode(' ', trim($t[$k]))[0]);
                }

                return $out;
            } catch (\Throwable $e) {
                return null;
            }
        }
    );

    $names = [
        'Fajr'    => 'ফজর',
        'Sunrise' => 'সূর্যোদয়',
        'Dhuhr'   => 'যোহর',
        'Asr'     => 'আসর',
        'Maghrib' => 'মাগরিব',
        'Isha'    => 'এশা',
    ];

    /** "16:41" → "বিকাল ৪:৪১" — the part of day, then the clock. */
    $pretty = function (string $hhmm): string {
        [$h, $m] = array_map('intval', array_pad(explode(':', $hhmm), 2, 0));

        $part = match (true) {
            $h < 4  => 'রাত',
            $h < 12 => 'সকাল',
            $h < 15 => 'দুপুর',
            $h < 18 => 'বিকাল',
            $h < 20 => 'সন্ধ্যা',
            default => 'রাত',
        };

        $h12 = $h % 12 === 0 ? 12 : $h % 12;

        return $part.' '.Bangla::digits($h12.':'.str_pad((string) $m, 2, '0', STR_PAD_LEFT));
    };
@endphp

@if ($enabled && $times)
    <section class="block prayer-card" data-prayer>
        <div class="block-head">
            <h2 class="block-title">নামাজের সময়</h2>
            <span class="block-more">{{ $city }}</span>
        </div>

        {{-- Filled in by the script below; without JavaScript the table is
             still the whole answer, so nothing important lives only here. --}}
        <div class="pr-next" data-prayer-next hidden>
            <span class="pr-next-name" data-prayer-next-name></span>
            <span class="pr-next-left" data-prayer-next-left></span>
        </div>

        <ul class="pr-list">
            @foreach ($times as $key => $value)
                <li class="pr-row" data-prayer-row="{{ $key }}" data-time="{{ $value }}">
                    <span class="pr-name">{{ $names[$key] ?? $key }}</span>
                    <span class="pr-time">{{ $pretty($value) }}</span>
                </li>
            @endforeach
        </ul>
    </section>

    @push('scripts')
    <script>
    (function () {
        var card = document.querySelector('[data-prayer]');
        if (!card) return;

        var rows   = Array.prototype.slice.call(card.querySelectorAll('[data-prayer-row]'));
        var banner = card.querySelector('[data-prayer-next]');
        var nameEl = card.querySelector('[data-prayer-next-name]');
        var leftEl = card.querySelector('[data-prayer-next-left]');
        if (!rows.length) return;

        // Sunrise is on the list for reference; it is not a prayer, so the
        // countdown skips over it.
        var waitable = rows.filter(function (r) {
            return r.getAttribute('data-prayer-row') !== 'Sunrise';
        });

        var bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];

        function toBangla(s) {
            return String(s).replace(/[0-9]/g, function (d) { return bn[+d]; });
        }

        function minutesOf(row) {
            var p = row.getAttribute('data-time').split(':');
            return (+p[0]) * 60 + (+p[1]);
        }

        function tick() {
            var now  = new Date();
            var mins = now.getHours() * 60 + now.getMinutes();
            var next = null;

            for (var i = 0; i < waitable.length; i++) {
                if (minutesOf(waitable[i]) > mins) { next = waitable[i]; break; }
            }

            rows.forEach(function (r) { r.classList.remove('is-next'); });

            if (!next) {
                // Past Isha: the next one is tomorrow's Fajr.
                nameEl.textContent = 'পরবর্তী ' + waitable[0].querySelector('.pr-name').textContent;
                leftEl.textContent = 'আগামীকাল';
                waitable[0].classList.add('is-next');
                banner.hidden = false;
                return;
            }

            next.classList.add('is-next');

            var left = minutesOf(next) * 60
                     - (now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds());
            var h = Math.floor(left / 3600);
            var m = Math.floor((left % 3600) / 60);

            nameEl.textContent = 'পরবর্তী ' + next.querySelector('.pr-name').textContent;
            leftEl.textContent = (h > 0 ? toBangla(h) + ' ঘণ্টা ' : '') + toBangla(m) + ' মিনিট বাকি';
            banner.hidden = false;
        }

        tick();
        setInterval(tick, 30000);
    })();
    </script>
    @endpush
@endif
