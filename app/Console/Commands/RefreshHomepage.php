<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Brings the homepage back to life after the WordPress import.
 *
 * The seeder built the homepage around demo content. Deleting the demo
 * articles left three kinds of hole:
 *
 *   · sections pointing at a category that now has no articles
 *   · "featured", "editor's pick" and "trending" sections, which read a flag
 *     on each article — and imported articles arrive with every flag off
 *   · the breaking ticker, which reads the same kind of flag
 *
 * This fills all three from the newest imported articles, so the client sees
 * a working front page. Everything it sets is an ordinary admin field: the
 * editor changes any of it from the admin afterwards, and this command only
 * ever touches what is still empty.
 *
 *   php artisan site:refresh-home --dry-run
 *   php artisan site:refresh-home
 *   php artisan site:refresh-home --force   re-pick even where flags exist
 */
class RefreshHomepage extends Command
{
    protected $signature = 'site:refresh-home {--dry-run} {--force}';

    protected $description = 'Point the homepage sections and the breaking ticker at real articles';

    protected bool $dry   = false;
    protected bool $force = false;

    /** How many articles each flag should cover. */
    protected array $quota = [
        'is_breaking'     => 8,
        'is_featured'     => 6,
        'is_editors_pick' => 5,
        'is_trending'     => 5,
    ];

    public function handle(): int
    {
        $this->dry   = (bool) $this->option('dry-run');
        $this->force = (bool) $this->option('force');

        if (Post::published()->doesntExist()) {
            $this->error('There are no published articles, so there is nothing to put on the homepage.');

            return self::FAILURE;
        }

        $this->line('');

        if ($this->dry) {
            $this->warn('Dry run — nothing will be saved.');
            $this->line('');
        }

        $this->flagArticles();
        $this->repointSections();

        if (! $this->dry) {
            Cache::forget('ticker.breaking');
            Cache::forget('home.sections');
            Cache::forget('footer.recent');
        }

        $this->line('');
        $this->info($this->dry ? 'Dry run finished.' : 'Homepage refreshed. Reload the site.');

        return self::SUCCESS;
    }

    /**
     * Set the breaking / featured / editor's pick / trending flags.
     *
     * A flag that already has articles behind it is left alone, so an editor's
     * own choices survive a re-run. --force overrides that.
     */
    protected function flagArticles(): void
    {
        $this->line('Article flags…');

        $offset = 0;

        foreach ($this->quota as $flag => $count) {
            $already = Post::published()->where($flag, true)->count();

            if ($already > 0 && ! $this->force) {
                $this->line("  · {$flag}: {$already} already set — left alone");
                $offset += $count;
                continue;
            }

            // Each flag takes a DIFFERENT slice of the newest articles.
            // Taking the newest N for every flag put the same four headlines
            // in the lead block, the editor's pick and the trending list at
            // once, which is what the homepage looked like on the first run.
            $ids = Post::published()
                ->latestFirst()
                ->skip($offset)
                ->take($count)
                ->pluck('id');

            $offset += $count;

            if ($ids->isEmpty()) {
                continue;
            }

            if ($this->dry) {
                $this->line("  + {$flag}: would set on {$ids->count()} newest article(s)");
                continue;
            }

            if ($this->force) {
                Post::where($flag, true)->update([$flag => false]);

                if ($flag === 'is_breaking') {
                    Post::whereNotNull('breaking_since')->update(['breaking_since' => null]);
                }
            }

            Post::whereIn('id', $ids)->update([$flag => true]);

            // Breaking is time-limited, so it needs its clock started —
            // a mass update does not run the model's saving hook.
            if ($flag === 'is_breaking') {
                Post::whereIn('id', $ids)->update(['breaking_since' => now()]);
            }

            // Trending is an ordered list, not a set — without a position the
            // section falls back to whatever order MySQL feels like.
            if ($flag === 'is_trending') {
                foreach ($ids->values() as $i => $id) {
                    Post::where('id', $id)->update(['trending_position' => $i + 1]);
                }
            }

            $this->line("  + {$flag}: set on {$ids->count()} article(s)");
        }
    }

    /**
     * Move any category section whose category is now empty onto a category
     * that actually has articles, biggest first, without giving two sections
     * the same one while unused categories remain.
     */
    protected function repointSections(): void
    {
        $this->line('');
        $this->line('Homepage sections…');

        $counts = Post::published()
            ->selectRaw('category_id, count(*) as total')
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->pluck('total', 'category_id')
            ->sortDesc();

        if ($counts->isEmpty()) {
            $this->warn('  No article has a category, so sections cannot be repointed.');

            return;
        }

        $names = Category::whereIn('id', $counts->keys())->pluck('name', 'id');

        $sections = HomeSection::where('source', 'category')
            ->orderBy('sort_order')
            ->get();

        if ($sections->isEmpty()) {
            $this->line('  No category sections to check.');

            return;
        }

        // Categories already in use by a section that still works.
        $taken = $sections
            ->filter(fn ($s) => $s->category_id && ($counts[$s->category_id] ?? 0) > 0)
            ->pluck('category_id')
            ->all();

        $available = $counts->keys()->reject(fn ($id) => in_array($id, $taken, true))->values();
        $next      = 0;

        foreach ($sections as $section) {
            $current = $section->category_id;
            $has     = $current ? ($counts[$current] ?? 0) : 0;

            if ($has > 0) {
                $this->line('  · '.$this->label($section).' → '.($names[$current] ?? '#'.$current)." ({$has}) — fine");
                continue;
            }

            // Prefer the category whose name matches the section's own title —
            // a section headed "জাতীয়" belongs on "জাতীয় সাক্ষ্য", not on
            // whichever category happens to be the largest.
            $pick = $this->matchByTitle($section, $names, $counts);

            if ($pick === null) {
                // No name match: fall back to the biggest unused category
                // rather than leave the section blank.
                $pick = $available[$next] ?? $counts->keys()->first();
                $next++;
            }

            $name  = $names[$pick] ?? ('#'.$pick);
            $total = $counts[$pick] ?? 0;

            if ($this->dry) {
                $this->line('  + '.$this->label($section)." → would point at {$name} ({$total})");
                continue;
            }

            $section->update(['category_id' => $pick]);
            $this->line('  + '.$this->label($section)." → {$name} ({$total})");
        }
    }

    /**
     * Find a category whose name matches the section's heading.
     *
     * WordPress named two of these with a suffix the client does not use —
     * the section says "জাতীয়", the category is "জাতীয় সাক্ষ্য" — so a
     * containment test either way is what actually matches in practice.
     * Only categories that hold articles are considered.
     */
    protected function matchByTitle(HomeSection $section, $names, $counts): ?int
    {
        $title = $this->normalise($section->title);

        if ($title === '') {
            return null;
        }

        $best     = null;
        $bestSize = -1;

        foreach ($names as $id => $name) {
            if (($counts[$id] ?? 0) < 1) {
                continue;
            }

            $candidate = $this->normalise($name);

            if ($candidate === '') {
                continue;
            }

            $hit = $candidate === $title
                || str_contains($candidate, $title)
                || str_contains($title, $candidate);

            // On a tie, the category holding more articles wins.
            if ($hit && $counts[$id] > $bestSize) {
                $best     = (int) $id;
                $bestSize = $counts[$id];
            }
        }

        return $best;
    }

    protected function normalise(?string $text): string
    {
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    protected function label(HomeSection $section): string
    {
        return ($section->title ?: $section->layout).' ['.$section->layout.']';
    }
}
