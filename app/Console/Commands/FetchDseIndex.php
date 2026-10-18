<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Support\Bangla;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Reads the DSEX index off dsebd.org and stores it for the sidebar card.
 *
 * The exchange publishes no free data feed, so this reads their public page
 * instead. That is a real dependency on someone else's markup: when DSE
 * redesign, this stops finding the number and says so rather than writing a
 * wrong one. It never overwrites a good figure with a guess.
 *
 * Run it once a day from the host's cron panel, after the market closes:
 *
 *   cd ~/apps/amardesh24 && /usr/bin/php artisan site:dse-index
 *
 *   php artisan site:dse-index --dry-run    find it, write nothing
 *   php artisan site:dse-index --debug      print what the page says around
 *                                           "DSEX", for when parsing fails
 */
class FetchDseIndex extends Command
{
    protected $signature = 'site:dse-index {--url=} {--dry-run} {--debug} {--links}';

    protected $description = 'Fetch the DSEX index from dsebd.org into the sidebar card';

    /** Tried in order; the first page that yields a number wins. */
    /*
     * Their own pages move about — two of the three I started with now
     * answer 410, and the homepage carries "DSEX" only as a label with no
     * figure beside it. These are the remaining candidates; --links lists
     * what the homepage actually points at, for when they move again.
     */
    protected array $sources = [
        'https://www.dsebd.org/market_summary.php',
        'https://www.dsebd.org/dse_market_summary.php',
        'https://www.dsebd.org/index_chart_data.php',
        'https://www.dsebd.org/latest_share_price_scroll_by_ltp.php',
        'https://www.dsebd.org/',
    ];

    public function handle(): int
    {
        $urls = $this->option('url') ? [$this->option('url')] : $this->sources;

        foreach ($urls as $url) {
            $this->line('Reading '.$url.' …');

            $html = $this->fetch($url);

            if ($html === null) {
                continue;
            }

            if ($this->option('links')) {
                $this->showLinks($html, $url);
            }

            $text = $this->toText($html);

            if ($this->option('debug')) {
                $this->showContext($text);
            }

            $found = $this->parse($text);

            if ($found === null) {
                $this->warn('  could not find a DSEX figure on this page');
                continue;
            }

            return $this->store($found);
        }

        $this->line('');
        $this->error('No DSEX figure found on any of the pages tried.');
        $this->line('Run again with --debug and send me the output — their');
        $this->line('markup has probably changed and I will adjust the pattern.');
        $this->line('');
        $this->comment('Nothing was overwritten; the card still shows the last good figure.');

        return self::FAILURE;
    }

    protected function fetch(string $url): ?string
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    // Their server refuses a request with no user agent.
                    'User-Agent' => 'Mozilla/5.0 (compatible; amarDesh24/1.0; +https://amardesh24.news)',
                    'Accept'     => 'text/html,application/xhtml+xml',
                ])
                ->get($url);

            if ($response->failed()) {
                $this->warn('  HTTP '.$response->status());

                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            $this->warn('  '.$e->getMessage());

            return null;
        }
    }

    /** Markup to readable text, with the whitespace collapsed. */
    protected function toText(string $html): string
    {
        $html = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', ' ', $html);
        $html = preg_replace('#<(br|/tr|/td|/th|/div|/p|/li)\s*/?>#i', ' | ', $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/[ \t\x{00A0}]+/u', ' ', preg_replace('/\s*\|\s*/', ' | ', $text)));
    }

    /**
     * Pull the index and its change out of the page text.
     *
     * DSE print it as "DSEX 5,432.10 ... -12.34 ... -0.23%", with the layout
     * changing between their pages, so each piece is looked for separately
     * within a window after the label rather than as one fixed pattern.
     */
    protected function parse(string $text): ?array
    {
        if (! preg_match('/DSEX\b(.{0,240})/is', $text, $m)) {
            return null;
        }

        $window = $m[1];

        // The first number that looks like an index: four or five digits
        // with a decimal. A change ("-12.34") has too few digits to match.
        if (! preg_match('/\b([1-9][0-9]{0,2}(?:,[0-9]{3})+(?:\.[0-9]{1,2})?|[1-9][0-9]{3,5}\.[0-9]{1,2})\b/', $window, $i)) {
            return null;
        }

        $index = $i[1];

        // The percentage, if it is printed. A bare point change is no use
        // to the card, which shows a percentage.
        $change = null;

        if (preg_match('/([+-]?[0-9]+(?:\.[0-9]{1,2})?)\s*%/', $window, $c)) {
            $change = $c[1];
        }

        return ['index' => $index, 'change' => $change];
    }

    protected function store(array $found): int
    {
        $this->line('');
        $this->info('  DSEX '.$found['index'].($found['change'] !== null ? '   '.$found['change'].'%' : '   (no % on the page)'));
        $this->line('');

        if ($this->option('dry-run')) {
            $this->comment('Dry run — nothing was saved.');

            return self::SUCCESS;
        }

        Setting::put('dse_index', $found['index']);

        if ($found['change'] !== null) {
            Setting::put('dse_change', $found['change']);
        }

        Setting::put('dse_as_of', Bangla::date(now()));

        Cache::forget('home.sections');

        $this->info('Saved. The sidebar card will show it on the next page load.');

        return self::SUCCESS;
    }

    /**
     * List the page's own links, so we can see where they have moved the
     * market summary to rather than guessing at URLs.
     */
    protected function showLinks(string $html, string $base): void
    {
        preg_match_all('#<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', $html, $m, PREG_SET_ORDER);

        $this->line('');
        $this->comment('--- links on this page ---');

        $seen = [];

        foreach ($m as $a) {
            $href  = trim($a[1]);
            $label = trim(preg_replace('/\s+/', ' ', strip_tags($a[2])));

            // Only their own pages, and only ones whose wording suggests
            // they might carry an index.
            if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript')) {
                continue;
            }

            if (! preg_match('/index|market|summary|dsex|chart|price/i', $href.' '.$label)) {
                continue;
            }

            if (isset($seen[$href])) {
                continue;
            }

            $seen[$href] = true;
            $this->line('  '.str_pad(mb_substr($label, 0, 38), 40).$href);
        }

        if ($seen === []) {
            $this->line('  (nothing on this page looks like a market or index link)');
        }

        $this->comment('--- end links ---');
        $this->line('');
    }

    /** For when the pattern misses: show what is actually around the label. */
    protected function showContext(string $text): void
    {
        $at = stripos($text, 'DSEX');

        $this->line('');
        $this->comment('--- debug ---');

        if ($at === false) {
            $this->line('The word "DSEX" does not appear on this page at all.');
            $this->line('First 400 characters of what came back:');
            $this->line(mb_substr($text, 0, 400));
        } else {
            $this->line('Text around "DSEX":');
            $this->line(mb_substr($text, max(0, $at - 120), 400));
        }

        $this->comment('--- end debug ---');
        $this->line('');
    }
}
