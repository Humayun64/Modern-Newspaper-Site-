<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Menu extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    /**
     * The menu for a location as nested plain arrays.
     *
     * Arrays, not models, for two reasons: the cache store serialises whatever
     * it is handed, and resolving each item's target separately would be a
     * query per link. Categories and pages are loaded once and matched in memory.
     */
    public static function tree(string $location): array
    {
        return Cache::remember("menu.tree.{$location}", 600, function () use ($location) {
            $menu = static::where('location', $location)->where('is_active', true)->first();

            if (! $menu) {
                return [];
            }

            $items = MenuItem::where('menu_id', $menu->id)->orderBy('sort_order')->get();

            if ($items->isEmpty()) {
                return [];
            }

            $categories = Category::whereIn('id', $items->where('type', 'category')->pluck('reference_id')->filter())
                ->get(['id', 'name', 'slug'])->keyBy('id');

            $pages = Page::whereIn('id', $items->where('type', 'page')->pluck('reference_id')->filter())
                ->published()->get(['id', 'title', 'slug'])->keyBy('id');

            $build = function (?int $parentId) use (&$build, $items, $categories, $pages) {
                $out = [];

                foreach ($items->where('parent_id', $parentId) as $item) {
                    $url = match ($item->type) {
                        'home'     => route('home'),
                        'category' => isset($categories[$item->reference_id])
                                        ? route('category.show', $categories[$item->reference_id]->slug)
                                        : null,
                        'page'     => isset($pages[$item->reference_id])
                                        ? route('page.show', $pages[$item->reference_id]->slug)
                                        : null,
                        default    => $item->custom_url ?: null,
                    };

                    // A link whose category or page was deleted, or an unpublished
                    // page, is dropped rather than rendered as a dead link.
                    if ($url === null) {
                        continue;
                    }

                    $out[] = [
                        'label'    => $item->label,
                        'url'      => $url,
                        'target'   => $item->target,
                        'children' => $build($item->id),
                    ];
                }

                return $out;
            };

            return $build(null);
        });
    }

    public static function forget(string $location): void
    {
        Cache::forget("menu.tree.{$location}");
    }
}
