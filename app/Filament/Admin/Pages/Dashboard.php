<?php

namespace App\Filament\Admin\Pages;

use App\Models\Category;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\DB;

/**
 * Newsroom dashboard.
 *
 * Extends Filament's own Dashboard so the /admin route and nav entry stay
 * intact, and swaps the widget grid for one Blade view. Same reasoning as the
 * settings page: this screen should survive Filament upgrades.
 */
class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.admin.pages.dashboard';

    public function getTitle(): string
    {
        return 'Newsroom';
    }

    public function getViewData(): array
    {
        return [
            'counts'    => $this->counts(),
            'activity'  => $this->activity(),
            'attention' => $this->attention(),
            'top'       => $this->topStories(),
            'byCat'     => $this->byCategory(),
            'recent'    => $this->recent(),
        ];
    }

    // ------------------------------------------------------------------
    // headline numbers
    // ------------------------------------------------------------------

    protected function counts(): array
    {
        $publishedThisWeek = Post::published()
            ->where('published_at', '>=', now()->subDays(7))
            ->count();

        $publishedPrevWeek = Post::published()
            ->whereBetween('published_at', [now()->subDays(14), now()->subDays(7)])
            ->count();

        return [
            'today'      => Post::published()->whereDate('published_at', today())->count(),
            'week'       => $publishedThisWeek,
            'week_delta' => $publishedThisWeek - $publishedPrevWeek,
            'drafts'     => Post::where('status', 'draft')->count(),
            'pending'    => Post::where('status', 'pending')->count(),
            'scheduled'  => $this->scheduledQuery()->count(),
            'reads'      => (int) Post::query()->sum('views'),
            'total'      => Post::published()->count(),
        ];
    }

    /** Anything queued to appear later: explicitly scheduled, or dated forward. */
    protected function scheduledQuery()
    {
        return Post::query()->where(function ($q) {
            $q->where('status', 'scheduled')
              ->orWhere(fn ($s) => $s->where('status', 'published')->where('published_at', '>', now()));
        });
    }

    // ------------------------------------------------------------------
    // publishing activity — articles per day, last 14 days
    // ------------------------------------------------------------------

    protected function activity(): array
    {
        $start = CarbonImmutable::today()->subDays(13);

        $rows = Post::published()
            ->where('published_at', '>=', $start->startOfDay())
            ->select(DB::raw('DATE(published_at) as day'), DB::raw('COUNT(*) as total'))
            ->groupBy('day')
            ->pluck('total', 'day');

        $days = [];
        for ($i = 0; $i < 14; $i++) {
            $date = $start->addDays($i);
            $days[] = [
                'date'  => $date->format('M j'),
                'short' => $date->format('j'),
                'count' => (int) ($rows[$date->format('Y-m-d')] ?? 0),
            ];
        }

        return [
            'days' => $days,
            'max'  => max(1, max(array_column($days, 'count'))),
            'sum'  => array_sum(array_column($days, 'count')),
        ];
    }

    // ------------------------------------------------------------------
    // what needs a human
    // ------------------------------------------------------------------

    protected function attention(): array
    {
        $items = [];

        if ($pending = Post::where('status', 'pending')->count()) {
            $items[] = [
                'status' => 'critical',
                'icon'   => '!',
                'label'  => 'Waiting for review',
                'count'  => $pending,
                'hint'   => 'Submitted by an author and not yet published.',
            ];
        }

        if ($drafts = Post::where('status', 'draft')->count()) {
            $items[] = [
                'status' => 'warning',
                'icon'   => '◑',
                'label'  => 'Unfinished drafts',
                'count'  => $drafts,
                'hint'   => 'Started but never published.',
            ];
        }

        $noImage = Post::published()
            ->where(fn ($q) => $q->whereNull('featured_image')->orWhere('featured_image', ''))
            ->count();

        if ($noImage) {
            $items[] = [
                'status' => 'serious',
                'icon'   => '▣',
                'label'  => 'Published with no image',
                'count'  => $noImage,
                'hint'   => 'These look broken in every homepage block and when shared.',
            ];
        }

        $noMeta = Post::published()
            ->where(fn ($q) => $q->whereNull('meta_description')->orWhere('meta_description', ''))
            ->count();

        if ($noMeta) {
            $items[] = [
                'status' => 'serious',
                'icon'   => '⌕',
                'label'  => 'Missing meta description',
                'count'  => $noMeta,
                'hint'   => 'Google writes its own snippet when this is empty.',
            ];
        }

        if ($scheduled = $this->scheduledQuery()->count()) {
            $items[] = [
                'status' => 'good',
                'icon'   => '◷',
                'label'  => 'Queued to go live',
                'count'  => $scheduled,
                'hint'   => 'Publishing automatically at their set time.',
            ];
        }

        return $items;
    }

    // ------------------------------------------------------------------
    // lists
    // ------------------------------------------------------------------

    protected function topStories()
    {
        return Post::published()
            ->with('category:id,name')
            ->where('published_at', '>=', now()->subDays(30))
            ->orderByDesc('views')
            ->limit(6)
            ->get(['id', 'title', 'slug', 'views', 'category_id']);
    }

    protected function byCategory()
    {
        $cats = Category::query()
            ->withCount(['posts' => fn ($q) => $q->published()->where('published_at', '>=', now()->subDays(30))])
            ->orderByDesc('posts_count')
            ->limit(7)
            ->get(['id', 'name']);

        $max = max(1, (int) $cats->max('posts_count'));

        return ['rows' => $cats, 'max' => $max];
    }

    protected function recent()
    {
        return Post::published()
            ->with(['author:id,name,name_bn', 'category:id,name'])
            ->latestFirst()
            ->limit(6)
            ->get(['id', 'title', 'slug', 'published_at', 'author_id', 'category_id', 'views']);
    }
}
