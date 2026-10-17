<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Clears the demo articles that were seeded while the site was being built.
 *
 * Imported articles carry a wp_id; seeded ones do not. That is the whole
 * test — it cannot mistake a real article for a demo one, because every
 * article that came from WordPress has that column filled in.
 *
 * Anything written by hand in the admin ALSO has no wp_id, so the command
 * lists what it is about to remove and asks before touching anything.
 *
 *   php artisan site:remove-demo --dry-run    list them, delete nothing
 *   php artisan site:remove-demo              delete, with a confirmation
 *   php artisan site:remove-demo --categories also remove empty categories
 */
class RemoveDemoContent extends Command
{
    protected $signature = 'site:remove-demo {--dry-run} {--categories}';

    protected $description = 'Delete the demo articles seeded before the WordPress import';

    public function handle(): int
    {
        if (! Schema::hasColumn((new Post)->getTable(), 'wp_id')) {
            $this->error('This project has no wp_id column, so demo and imported articles cannot be told apart.');

            return self::FAILURE;
        }

        $imported = Post::withTrashed()->whereNotNull('wp_id')->count();

        if ($imported === 0) {
            $this->error('No imported articles found. Run import:wordpress first —');
            $this->line('otherwise this would delete everything on the site.');

            return self::FAILURE;
        }

        $demo = Post::withTrashed()->whereNull('wp_id')->get(['id', 'title', 'created_at']);

        $this->line('');
        $this->info(number_format($imported).' imported articles will be kept.');

        if ($demo->isEmpty()) {
            $this->info('No demo articles found — nothing to remove.');

            // The articles may have gone on an earlier run while the leftover
            // categories are still there, so --categories still has work to do.
            if ($this->option('categories') && ! $this->option('dry-run')) {
                $this->line('');
                $this->removeEmptyCategories();
            }

            return self::SUCCESS;
        }

        $this->line('');
        $this->warn(number_format($demo->count()).' article(s) have no wp_id and would be deleted:');

        foreach ($demo->take(40) as $post) {
            $this->line('  · '.\Illuminate\Support\Str::limit($post->title, 70).'   ('.$post->created_at?->format('d M Y').')');
        }

        if ($demo->count() > 40) {
            $this->line('  … and '.number_format($demo->count() - 40).' more');
        }

        $this->line('');
        $this->comment('Anything you wrote by hand in the admin is in this list too — read it before saying yes.');
        $this->line('');

        if ($this->option('dry-run')) {
            $this->info('Dry run — nothing was deleted.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Permanently delete these '.$demo->count().' article(s)?', false)) {
            $this->line('Cancelled. Nothing was deleted.');

            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($demo as $row) {
            $post = Post::withTrashed()->find($row->id);

            if (! $post) {
                continue;
            }

            // Detach first: forceDelete leaves orphaned pivot rows on some
            // MySQL setups where the foreign keys were added without cascade.
            $post->categories()->detach();
            $post->tags()->detach();
            $post->forceDelete();
            $deleted++;
        }

        $this->info(number_format($deleted).' demo article(s) deleted.');

        if ($this->option('categories')) {
            $this->removeEmptyCategories();
        }

        Cache::forget('home.sections');
        Cache::forget('footer.recent');
        Cache::forget('menu.tree.header');
        Cache::forget('menu.tree.footer');

        $this->line('');
        $this->comment('Check the homepage. If a section looks empty, it was pointing at a demo category.');

        return self::SUCCESS;
    }

    /**
     * Only categories that came from the seeder — no wp_term_id — and that
     * now hold nothing. A category you made yourself and left empty on
     * purpose is kept.
     */
    protected function removeEmptyCategories(): void
    {
        $candidates = Category::query()
            ->when(
                Schema::hasColumn((new Category)->getTable(), 'wp_term_id'),
                fn ($q) => $q->whereNull('wp_term_id')
            )
            ->get();

        // A category sitting in a menu is there on purpose. বিজ্ঞাপন,
        // সাক্ষাৎকার and স্বাস্থ্য were created empty for the client's menu
        // and must survive this.
        $inMenus = \App\Models\MenuItem::where('type', 'category')
            ->whereNotNull('reference_id')
            ->pluck('reference_id')
            ->all();

        $removed = 0;

        foreach ($candidates as $category) {
            if (in_array($category->id, $inMenus)) {
                continue;
            }

            if (\App\Models\HomeSection::where('category_id', $category->id)->exists()) {
                continue;
            }

            if (method_exists($category, 'posts') && $category->posts()->exists()) {
                continue;
            }

            // Belt and braces: the pivot may hold rows even when the model
            // has no posts() relation to ask through.
            if (Post::withTrashed()->where('category_id', $category->id)->exists()) {
                continue;
            }

            $this->line('  removing empty category: '.$category->name);
            $category->delete();
            $removed++;
        }

        $this->info(number_format($removed).' empty category(ies) removed.');
    }
}
