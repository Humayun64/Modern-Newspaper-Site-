<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Models\HomeSection;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * The corrections that are database values rather than code.
 *
 * Each of these could be typed into the admin by hand. They are here because
 * typing Bangla into a form is where mistakes creep in, and because this can
 * be run on the live server as easily as locally.
 *
 * It only ever fills what is empty, so a second run reports "already set"
 * and leaves anything the client has since edited alone. --force overwrites.
 *
 *   php artisan site:apply-corrections --dry-run
 *   php artisan site:apply-corrections
 *   php artisan site:apply-corrections --force
 */
class ApplyCorrections extends Command
{
    protected $signature = 'site:apply-corrections {--dry-run} {--force}';

    protected $description = 'Apply the client corrections that live in the database';

    /** Correction 18: who the editorial card is about. */
    protected array $settings = [
        'editor_name'       => 'ইঞ্জিনিয়ার শফিকুল ইসলাম',
        'editor_role'       => 'প্রতিষ্ঠাতা ও স্বত্বাধিকারী, আমারদেশ২৪.নিউজ',
        'editor_bio'        => 'সত্য, নিরপেক্ষতা ও বস্তুনিষ্ঠ সাংবাদিকতার অঙ্গীকার নিয়ে পাঠকদের কাছে নির্ভরযোগ্য সংবাদ পৌঁছে দেওয়াই আমাদের লক্ষ্য।',
        // Correction 22, the sidebar card.
        'editorial_heading' => 'পরিচালকের পরিচিতি',
    ];

    /** Correction 22: the homepage block. [old title => new title] */
    protected array $sectionTitles = [
        'সম্পাদকের পছন্দ' => 'সম্পাদকের কলাম',
    ];

    public function handle(): int
    {
        $dry   = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $this->line('');

        $this->applySettings($dry, $force);
        $this->renameSections($dry);
        $this->recolourBanner($dry, $force);

        if (! $dry) {
            Cache::forget('home.sections');
            Cache::forget('footer.recent');
        }

        $this->line('');
        $this->info($dry ? 'Dry run — nothing was saved.' : 'Done. Reload the site.');

        return self::SUCCESS;
    }

    protected function applySettings(bool $dry, bool $force): void
    {
        $this->line('Editorial card (18, 22)…');

        $current = Setting::all_cached();

        foreach ($this->settings as $key => $value) {
            $existing = trim((string) ($current[$key] ?? ''));

            if ($existing !== '' && ! $force) {
                $this->line('  · '.$key.': already set — left alone');
                continue;
            }

            if ($dry) {
                $this->line('  + '.$key.': would set');
                continue;
            }

            Setting::put($key, $value);
            $this->line('  + '.$key.': set');
        }
    }

    protected function renameSections(bool $dry): void
    {
        $this->line('');
        $this->line('Homepage section titles (22)…');

        foreach ($this->sectionTitles as $from => $to) {
            $sections = HomeSection::where('title', $from)->get();

            if ($sections->isEmpty()) {
                $this->line('  · "'.$from.'": not found — nothing to rename');
                continue;
            }

            foreach ($sections as $section) {
                if ($dry) {
                    $this->line('  + "'.$from.'" → "'.$to.'" (would rename)');
                    continue;
                }

                $section->update(['title' => $to]);
                $this->line('  + "'.$from.'" → "'.$to.'"');
            }
        }
    }

    /**
     * Correction 1: the masthead banner.
     *
     * That purple block is a demo Ad record, not part of the theme — the
     * client can replace it with their own image under Ads at any time. Until
     * they do, it at least matches the site instead of shouting at it, so the
     * placeholder is repainted in the site's own accent colour.
     */
    protected function recolourBanner(bool $dry, bool $force): void
    {
        $this->line('');
        $this->line('Masthead banner (1)…');

        $ad = Ad::where('placement', 'header')->first();

        if (! $ad) {
            $this->line('  · no header banner set up — nothing to repaint');

            return;
        }

        if (filled($ad->image)) {
            $this->line('  · a real banner image is in place — left alone');

            return;
        }

        if (filled($ad->code) && ! str_contains((string) $ad->code, 'data-placeholder') && ! $force) {
            $this->line('  · the banner carries custom code — left alone (use --force to replace)');

            return;
        }

        $settings = Setting::all_cached();
        $accent   = $this->hex($settings['color_accent'] ?? '', '#d32b2b');
        $dark     = $this->hex($settings['color_accent_dk'] ?? '', '#a81f1f');

        $code = <<<HTML
        <div data-placeholder="header-banner"
             style="background:linear-gradient(100deg,{$dark},{$accent});color:#fff;
                    padding:28px 22px;border-radius:4px;text-align:center">
            <div style="font-size:19px;font-weight:700;letter-spacing:.6px">বিজ্ঞাপনের স্থান</div>
            <div style="font-size:12.5px;opacity:.85;margin-top:5px">
                ৬৪০ × ১০০ — অ্যাডমিন প্যানেলের Ads থেকে বদলানো যাবে
            </div>
        </div>
        HTML;

        if ($dry) {
            $this->line('  + would repaint the placeholder in '.$accent);

            return;
        }

        $ad->update(['code' => $code, 'image' => null]);
        $this->line('  + placeholder repainted in '.$accent);
    }

    /** Only let a real hex colour through — this goes into a style attribute. */
    protected function hex(string $value, string $fallback): string
    {
        $value = trim($value);

        return preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) ? $value : $fallback;
    }
}
