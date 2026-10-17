<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\HomeSection;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the homepage sections the client asked for by name.
 *
 * There is no admin screen for the homepage layout yet, so the rows are
 * created here. It only ever adds what is missing: run it twice and the
 * second run reports that everything is already there.
 *
 * A section whose category holds no articles will not appear on the site
 * until it does — HomeController drops empty sections rather than printing
 * a heading with nothing under it. স্বাস্থ্য starts out empty, so the
 * section waits quietly until the first health article is published.
 *
 *   php artisan site:ensure-sections --dry-run
 *   php artisan site:ensure-sections
 */
class EnsureSections extends Command
{
    protected $signature = 'site:ensure-sections {--dry-run}';

    protected $description = 'Create the homepage sections asked for in the client corrections';

    /**
     * [heading shown on the site, category name, layout, how many articles].
     *
     * Both of these are "more" sections that belong down the page, so they
     * use the full-width list layouts rather than a hero or sidebar one.
     */
    protected array $wanted = [
        ['স্বাস্থ্য', 'স্বাস্থ্য', 'posts_grid', 6],
    ];

    // "বিশ্ব গণমাধ্যমে" is deliberately NOT here. Correction 20 wants
    // uploaded PDFs, not articles, so it has its own table and its own
    // admin screen, and the homepage includes it directly.

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->line('');

        $nextSort = (int) HomeSection::max('sort_order') + 1;
        $added    = 0;

        foreach ($this->wanted as [$title, $categoryName, $layout, $limit]) {
            $category = $this->findCategory($categoryName);

            if (! $category) {
                $this->warn("  ! {$title}: no category named \"{$categoryName}\" — create it first, then run this again.");
                continue;
            }

            $exists = HomeSection::where('category_id', $category->id)
                ->where('layout', $layout)
                ->exists();

            if ($exists) {
                $this->line("  · {$title}: already there");
                continue;
            }

            if ($dry) {
                $this->line("  + {$title}: would add ({$layout}, {$limit} articles, from {$category->name})");
                $added++;
                continue;
            }

            $data = [
                'title'       => $title,
                'layout'      => $layout,
                'source'      => 'category',
                'category_id' => $category->id,
                'limit'       => $limit,
                'is_active'   => true,
                'sort_order'  => $nextSort++,
            ];

            // The table has grown over time and the server copy may lag the
            // local one, so unknown columns are dropped rather than aborting.
            $table = (new HomeSection)->getTable();
            $data  = array_filter(
                $data,
                fn ($key) => Schema::hasColumn($table, $key),
                ARRAY_FILTER_USE_KEY
            );

            HomeSection::create($data);
            $this->line("  + {$title}: added");
            $added++;
        }

        if (! $dry) {
            Cache::forget('home.sections');
        }

        $this->line('');
        $this->info($dry
            ? "Dry run — {$added} section(s) would be added."
            : "{$added} section(s) added.");

        return self::SUCCESS;
    }

    /** Name match that ignores the zero-width characters Bangla text collects. */
    protected function findCategory(string $name): ?Category
    {
        $wanted = $this->normalise($name);

        return Category::all()->first(fn ($c) => $this->normalise($c->name) === $wanted);
    }

    protected function normalise(?string $text): string
    {
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
