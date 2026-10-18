<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Says why a sidebar card is not showing.
 *
 * The weather and prayer-times cards both hide themselves when their API
 * call fails — a news site should not print "could not reach the weather
 * service" to its readers. That is right for readers and useless for
 * whoever is wondering where the card went, so this answers that question.
 *
 *   php artisan site:check-widgets
 *   php artisan site:check-widgets --clear   also drop their caches
 */
class CheckWidgets extends Command
{
    protected $signature = 'site:check-widgets {--clear}';

    protected $description = 'Test the outbound calls the sidebar cards depend on';

    public function handle(): int
    {
        $s = Setting::all_cached();

        $lat = (float) ($s['weather_lat'] ?? 23.8103);
        $lon = (float) ($s['weather_lon'] ?? 90.4125);

        $this->line('');
        $this->line('PHP can reach HTTPS?');
        $this->line('  curl.cainfo     : '.(ini_get('curl.cainfo') ?: '(not set)'));
        $this->line('  openssl.cafile  : '.(ini_get('openssl.cafile') ?: '(not set)'));
        $this->line('');

        $checks = [
            'Weather'      => ['https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat, 'longitude' => $lon, 'current' => 'temperature_2m',
            ], 'current.temperature_2m'],

            'Air quality'  => ['https://air-quality-api.open-meteo.com/v1/air-quality', [
                'latitude' => $lat, 'longitude' => $lon, 'current' => 'us_aqi',
            ], 'current.us_aqi'],

            'Prayer times' => ['https://api.aladhan.com/v1/timings', [
                'latitude' => $lat, 'longitude' => $lon,
                'method'   => (int) ($s['prayer_method'] ?? 1), 'school' => 1,
            ], 'data.timings.Fajr'],
        ];

        $failed = 0;

        foreach ($checks as $name => [$url, $query, $path]) {
            $this->line(str_pad($name, 14).' → '.$url);

            try {
                $r = Http::timeout(8)->get($url, $query);

                if ($r->failed()) {
                    $this->error('                 HTTP '.$r->status());
                    $failed++;
                    continue;
                }

                $value = $r->json($path);

                if ($value === null) {
                    $this->warn('                 reached it, but "'.$path.'" was not in the reply');
                    $failed++;
                    continue;
                }

                $this->info('                 OK — '.$path.' = '.$value);
            } catch (\Throwable $e) {
                $this->error('                 '.$e->getMessage());
                $failed++;
            }

            $this->line('');
        }

        if ($this->option('clear')) {
            foreach (Cache::get('widget.keys', []) as $k) {
                Cache::forget($k);
            }

            Cache::forget('topbar.weather');
            Cache::forget('widget.weather.'.$lat.','.$lon);

            // The prayer key carries today's date and the method.
            Cache::forget('widget.prayer.'.now()->toDateString().'.'.$lat.','.$lon
                .'.'.(int) ($s['prayer_method'] ?? 1));

            $this->line('Caches cleared — the next page load fetches fresh.');
            $this->line('');
        }

        if ($failed === 0) {
            $this->info('All three reachable. If a card is still missing, it is cached —');
            $this->line('run this again with --clear and reload the page.');

            return self::SUCCESS;
        }

        $this->line('');
        $this->error($failed.' of '.count($checks).' could not be reached.');

        if (stripos(PHP_OS_FAMILY, 'Windows') !== false && ! ini_get('curl.cainfo')) {
            $this->line('');
            $this->comment('curl.cainfo is not set, which on Windows means PHP cannot verify');
            $this->comment('any HTTPS certificate — that is almost certainly the whole problem.');
            $this->comment('Add these two lines to '.php_ini_loaded_file().' and restart the server:');
            $this->line('');
            $this->line('  curl.cainfo = D:\Xammp\apache\bin\curl-ca-bundle.crt');
            $this->line('  openssl.cafile = D:\Xammp\apache\bin\curl-ca-bundle.crt');
        }

        return self::FAILURE;
    }
}
