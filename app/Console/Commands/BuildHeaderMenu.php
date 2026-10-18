<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Puts the header menu into the order the client asked for (correction 14).
 *
 * This is a one-shot convenience, not a seeder that runs on deploy: it
 * REPLACES whatever is in the header menu, so anything arranged by hand in
 * the menu builder afterwards would be lost by running it again. The menu
 * builder stays the normal way to change the menu.
 *
 *   php artisan site:header-menu --dry-run      show the plan, change nothing
 *   php artisan site:header-menu                build it, skip missing categories
 *   php artisan site:header-menu --create       build it, creating any missing category
 *   php artisan site:header-menu --keep-labels  leave the header buttons alone
 */
class BuildHeaderMenu extends Command
{
    protected $signature = 'site:header-menu {--dry-run} {--create} {--keep-labels}';

    protected $description = 'Rebuild the header menu in the order agreed with the client';

    /**
     * Top level, in order: [menu label, type, category to point at].
     *
     * Label and category are separate on purpose. WordPress named three of
     * these differently from the way the client wants them read — the menu
     * says "জাতীয়", the category behind it is "জাতীয় সাক্ষ্য". Renaming the
     * categories would change their URLs, so only the label differs.
     */
    protected array $plan = [
        ['হোম',          'home',     null],
        ['শীর্ষ খবর',     'category', 'শীর্ষ'],
        ['জাতীয়',        'category', 'জাতীয় সাক্ষ্য'],
        // Correction 6. Created empty by --create; the client fills it later.
        ['রাজনীতি',       'category', 'রাজনীতি'],
        ['আন্তর্জাতিক',    'category', 'আন্তর্জাতিক সাক্ষ্য'],
        ['যুদ্ধ',          'category', 'যুদ্ধ'],
        ['খেলাধুলা',      'category', 'খেলাধুলা'],
        ['পডকাস্ট',       'category', 'পডকাস্ট'],
    ];

    protected array $moreChildren = [
        ['টেক নিউজ',    'category', 'টেক নিউজ'],
        ['বিজ্ঞাপন',     'category', 'বিজ্ঞাপন'],
        ['সাক্ষাৎকার',   'category', 'সাক্ষাৎকার'],
        ['স্বাস্থ্য',       'category', 'স্বাস্থ্য'],
    ];

    /**
     * Older names the import may have produced, tried when the name above
     * finds nothing. Saves a re-run when WordPress and the client disagree.
     */
    protected array $categoryAliases = [
        'শীর্ষ'              => ['শীর্ষ খবর'],
        'জাতীয় সাক্ষ্য'      => ['জাতীয়'],
        'আন্তর্জাতিক সাক্ষ্য' => ['আন্তর্জাতিক'],
        'টেক নিউজ'          => ['টেক', 'টেক নিউজ'],
    ];

    public function handle(): int
    {
        $dry    = (bool) $this->option('dry-run');
        $create = (bool) $this->option('create');

        // Resolve every category up front so a missing one is reported before
        // anything is written, rather than leaving a half-built menu.
        $resolved = [];
        $missing  = [];

        foreach ([...$this->plan, ...$this->moreChildren] as [$label, $type, $target]) {
            if ($type !== 'category') {
                continue;
            }

            $category = $this->findCategory($target);

            if ($category) {
                $resolved[$target] = $category->id;
                continue;
            }

            $missing[] = $target;
        }

        if ($missing && ! $create) {
            $this->warn('These categories do not exist yet:');
            foreach ($missing as $name) {
                $this->line('  · '.$name);
            }
            $this->line('');
            $this->line('Run with --create to have them made automatically,');
            $this->line('or add them under Categories in the admin first.');
            $this->line('');
        }

        if ($missing && $create) {
            $this->info('These categories will be created:');
            foreach ($missing as $name) {
                $this->line('  + '.$name);
            }
            $this->line('');
        }

        $this->line('Header menu will be:');
        foreach ($this->plan as [$label, $type, $target]) {
            $ok = $type !== 'category' || isset($resolved[$target]) || $create;
            $this->line(($ok ? '  ✓ ' : '  ✗ ').$this->describe($label, $target, $resolved));
        }
        $this->line('  ✓ আরও');
        foreach ($this->moreChildren as [$label, $type, $target]) {
            $ok = isset($resolved[$target]) || $create;
            $this->line(($ok ? '      ✓ ' : '      ✗ ').$this->describe($label, $target, $resolved));
        }
        $this->line('');

        if ($dry) {
            $this->info('Dry run — nothing was changed.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Replace the current header menu with this?', true)) {
            $this->line('Cancelled.');

            return self::SUCCESS;
        }

        // Create the missing categories before the menu, outside the menu
        // transaction, so a category that fails to save does not roll the
        // whole menu back with it.
        if ($create) {
            foreach ($missing as $name) {
                try {
                    $category = $this->createCategory($name);
                    $resolved[$name] = $category->id;
                    $this->line('  + created '.$name);
                } catch (\Throwable $e) {
                    $this->error('  ! could not create '.$name.' — '.$e->getMessage());
                }
            }
            $this->line('');
        }

        DB::transaction(function () use ($resolved) {
            $menu = Menu::firstOrCreate(
                ['location' => 'header'],
                ['name' => 'Header menu', 'is_active' => true],
            );

            // Children cascade, so deleting the top level clears the whole tree.
            MenuItem::where('menu_id', $menu->id)->delete();

            $sort = 0;

            foreach ($this->plan as [$label, $type, $target]) {
                if ($type === 'category' && ! isset($resolved[$target])) {
                    continue;
                }

                MenuItem::create([
                    'menu_id'      => $menu->id,
                    'parent_id'    => null,
                    'label'        => $label,
                    'type'         => $type,
                    'reference_id' => $type === 'category' ? $resolved[$target] : null,
                    'custom_url'   => null,
                    'sort_order'   => $sort++,
                ]);
            }

            // "আরও" is a heading that opens the dropdown rather than a
            // destination of its own, so it carries a bare # link.
            $more = MenuItem::create([
                'menu_id'    => $menu->id,
                'parent_id'  => null,
                'label'      => 'আরও',
                'type'       => 'custom',
                'custom_url' => '#',
                'sort_order' => $sort++,
            ]);

            $childSort = 0;

            foreach ($this->moreChildren as [$label, $type, $target]) {
                if (! isset($resolved[$target])) {
                    continue;
                }

                MenuItem::create([
                    'menu_id'      => $menu->id,
                    'parent_id'    => $more->id,
                    'label'        => $label,
                    'type'         => 'category',
                    'reference_id' => $resolved[$target],
                    'sort_order'   => $childSort++,
                ]);
            }
        });

        Cache::forget('menu.tree.header');

        if (! $this->option('keep-labels')) {
            // Correction 10: the button reads "Latest Video". Stored rather
            // than hard-coded so the admin can still change it afterwards.
            Setting::put('nav_cta_text', 'Latest Video');
            Setting::put('nav_cta_url', '');
            Setting::put('nav_latest_text', 'সাম্প্রতিক');
            Setting::put('nav_latest_url', '');

            $this->line('Header buttons set to সাম্প্রতিক and Latest Video.');
        }

        $this->info('Header menu rebuilt.');

        return self::SUCCESS;
    }

    /** Shows the label, and the category behind it when the two differ. */
    protected function describe(string $label, ?string $target, array $resolved): string
    {
        if ($target === null || $target === $label) {
            return $label;
        }

        return $label.'   → '.$target;
    }

    /**
     * Match a category by name, then by its known aliases, then by slug.
     *
     * Bangla text can carry a zero-width joiner or a stray space that makes
     * two visually identical names compare as different, so the name match
     * is done on a normalised copy rather than with a plain where().
     */
    protected function findCategory(string $name): ?Category
    {
        $all = Category::query()->get(['id', 'name', 'slug']);

        foreach ([$name, ...($this->categoryAliases[$name] ?? [])] as $candidate) {
            $wanted = $this->normalise($candidate);

            $matches = $all->filter(fn ($c) => $this->normalise($c->name) === $wanted);

            if ($matches->isEmpty()) {
                continue;
            }

            // The seeder and the WordPress import can both leave a category
            // of the same name behind — "খেলাধুলা" with no articles from the
            // demo data, and "খেলাধুলা" with 150 from the import. Taking the
            // first row would silently pick the empty one, so the menu points
            // at whichever actually holds articles.
            return $matches
                ->sortByDesc(fn ($c) => $this->articleCount($c->id))
                ->first();
        }

        return Category::where('slug', $this->slug($name))->first();
    }

    /** Published articles in a category, counting both the column and the pivot. */
    protected function articleCount(int $categoryId): int
    {
        return Post::published()
            ->where(function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId)
                  ->orWhereHas('categories', fn ($c) => $c->where('categories.id', $categoryId));
            })
            ->count();
    }

    protected function normalise(?string $text): string
    {
        // Strip zero-width joiner / non-joiner / BOM, collapse whitespace.
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * Create a category, filling only the columns this install actually has.
     *
     * The categories table has grown over time and differs between the local
     * copy and the server, so each optional column is checked before it is
     * written — an unknown column would otherwise abort the whole command.
     */
    protected function createCategory(string $name): Category
    {
        $table = (new Category)->getTable();
        $data  = ['name' => $this->normalise($name)];

        if (Schema::hasColumn($table, 'slug')) {
            $data['slug'] = $this->uniqueSlug($name);
        }

        foreach (['is_active' => true, 'show_in_menu' => true, 'in_menu' => true, 'is_visible' => true] as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $data[$column] = $value;
            }
        }

        if (Schema::hasColumn($table, 'sort_order')) {
            $data['sort_order'] = (int) Category::max('sort_order') + 1;
        }

        if (Schema::hasColumn($table, 'description')) {
            $data['description'] = '';
        }

        return Category::create($data);
    }

    /** Bangla-safe slug. Str::slug() strips Bangla entirely and returns ''. */
    protected function slug(string $text): string
    {
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text);
        $text = preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s\-]/u', '', $text);
        $text = preg_replace('/[\s\-]+/u', '-', trim($text));

        return trim(mb_strtolower($text), '-');
    }

    protected function uniqueSlug(string $name): string
    {
        $base = $this->slug($name) ?: 'category';
        $slug = $base;
        $n    = 2;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }
}
