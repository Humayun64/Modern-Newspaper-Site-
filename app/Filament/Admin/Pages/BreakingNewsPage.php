<?php

namespace App\Filament\Admin\Pages;

use App\Models\Post;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Cache;

/**
 * Breaking news — choosing by hand what runs in the red strip.
 *
 * The strip used to be automatic: the four newest articles, always. That is
 * still what happens when nothing is chosen here, because an empty strip on
 * a news homepage reads as a broken site. The moment one story is marked,
 * the choices take over completely and the automatic list steps aside.
 *
 * No Filament table or form schema is used, on purpose. Those APIs move
 * between major versions and this screen has to survive upgrades; Livewire's
 * wire:click on a plain page does not move. The same reasoning as the
 * Global coverage screen beside it.
 */
class BreakingNewsPage extends Page
{
    protected string $view = 'filament.admin.pages.breaking-news';

    /** Bound to the search box with wire:model.live. */
    public string $search = '';

    /** Shown once after an action, so the editor knows it landed. */
    public string $notice = '';

    public static function getNavigationLabel(): string
    {
        return 'Breaking news';
    }

    public function getTitle(): string
    {
        return 'Breaking news';
    }

    public static function getNavigationSort(): ?int
    {
        return 15;
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    /** How many the strip shows — the same setting the front end reads. */
    public function limit(): int
    {
        return (int) (\App\Models\Setting::all_cached()['breaking_count'] ?? 4) ?: 4;
    }

    public function toggle(int $id): void
    {
        $post = Post::find($id);

        if (! $post) {
            $this->notice = 'That article no longer exists.';

            return;
        }

        $post->is_breaking = ! $post->is_breaking;
        $post->save();

        $this->notice = $post->is_breaking
            ? 'Added to the breaking strip.'
            : 'Removed from the breaking strip.';

        $this->forgetTicker();
    }

    public function clearAll(): void
    {
        Post::where('is_breaking', true)->update(['is_breaking' => false]);

        $this->notice = 'Cleared. The strip is back to showing the newest articles.';

        $this->forgetTicker();
    }

    /**
     * The strip is cached for a minute. Without this the editor marks a
     * story, reloads the site, sees no change, and marks it again.
     */
    protected function forgetTicker(): void
    {
        foreach ([4, 5, 6, 8, 10, $this->limit()] as $n) {
            Cache::forget('ticker.latest.'.$n);
            Cache::forget('ticker.latest.nv.'.$n);
            Cache::forget('ticker.chosen.'.$n);
        }
    }

    public function getViewData(): array
    {
        $chosen = Post::query()
            ->where('is_breaking', true)
            ->with('category:id,name')
            ->orderByDesc('published_at')
            ->get(['id', 'title', 'slug', 'category_id', 'published_at', 'status']);

        $candidates = Post::query()
            ->published()
            // Rows imported before the column existed can hold NULL, and
            // `where(..., false)` would quietly hide every one of them.
            ->where(fn ($q) => $q->where('is_breaking', false)->orWhereNull('is_breaking'))
            ->when($this->search !== '', fn ($q) => $q->where('title', 'like', '%'.$this->search.'%'))
            ->with('category:id,name')
            ->orderByDesc('published_at')
            ->limit(40)
            ->get(['id', 'title', 'slug', 'category_id', 'published_at', 'status']);

        return [
            'chosen'     => $chosen,
            'candidates' => $candidates,
            'limit'      => $this->limit(),
        ];
    }
}
