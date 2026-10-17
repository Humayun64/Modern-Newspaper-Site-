<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Correction 13: breaking headlines stop being breaking after a day.
 *
 * This clears the flag on anything older than the cut-off, so the admin list
 * does not silt up with last week's "breaking" stories and an editor can see
 * at a glance what is actually current.
 *
 * The public ticker applies the same age limit in its own query, so a missed
 * run here never leaves a stale headline on the site — this command is
 * housekeeping, not the safety net.
 *
 * Run it hourly from the host's cron panel:
 *
 *   cd ~/apps/amardesh24 && php artisan site:expire-breaking
 *
 *   php artisan site:expire-breaking --dry-run
 *   php artisan site:expire-breaking --hours=12
 */
class ExpireBreaking extends Command
{
    protected $signature = 'site:expire-breaking {--hours=} {--dry-run}';

    protected $description = 'Clear the breaking flag on articles older than the cut-off';

    public function handle(): int
    {
        // --hours wins, then the admin setting, then a day.
        $hours = (int) ($this->option('hours')
            ?: (Setting::all_cached()['breaking_hours'] ?? 24));

        if ($hours < 1) {
            $hours = 24;
        }

        $cutoff = now()->subHours($hours);

        $stale = Post::withTrashed()
            ->where('is_breaking', true)
            // Measured from when the flag went on, not from publish time:
            // an editor can mark an older story breaking and still get a
            // full day out of it.
            ->whereNotNull('breaking_since')
            ->where('breaking_since', '<', $cutoff)
            ->get(['id', 'title', 'breaking_since']);

        if ($stale->isEmpty()) {
            $this->info("Nothing to clear — no breaking article is older than {$hours}h.");

            return self::SUCCESS;
        }

        $this->line('');
        $this->line("Breaking for longer than {$hours}h:");

        foreach ($stale->take(20) as $post) {
            $this->line('  · '.\Illuminate\Support\Str::limit($post->title, 64)
                .'   (marked '.($post->breaking_since?->diffForHumans() ?? 'unknown').')');
        }

        if ($stale->count() > 20) {
            $this->line('  … and '.($stale->count() - 20).' more');
        }

        $this->line('');

        if ($this->option('dry-run')) {
            $this->info('Dry run — '.$stale->count().' article(s) would be cleared.');

            return self::SUCCESS;
        }

        Post::withTrashed()
            ->whereIn('id', $stale->pluck('id'))
            ->update(['is_breaking' => false, 'breaking_since' => null]);

        // The ticker caches per cut-off, so clear the handful of keys the
        // setting could plausibly have been set to rather than guessing one.
        Cache::forget('ticker.breaking');

        foreach ([6, 12, 18, 24, 36, 48, 72, $hours] as $h) {
            Cache::forget('ticker.breaking.'.$h);
        }

        $this->info($stale->count().' article(s) are no longer marked breaking.');

        return self::SUCCESS;
    }
}
