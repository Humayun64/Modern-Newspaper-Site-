<?php

namespace App\Support;

use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Keeps video stories out of the ordinary news listings.
 *
 * They have their own page — সর্বশেষ ভিডিও — and the client does not want
 * them competing with written news in the breaking strip or the সর্বশেষ
 * sidebar. This is the one place that decides what counts as a video
 * story, so the rule cannot drift apart between the two listings.
 *
 * An article is a video story if EITHER is true:
 *
 *   · its Type is Video, or
 *   · it sits in a video category — primary or additional
 *
 * Having a video link is deliberately NOT enough. An ordinary report can
 * carry a clip of the scene without becoming a video story, and excluding
 * those would quietly empty the news listings as the habit spread.
 */
class VideoContent
{
    /**
     * Ids of the video categories.
     *
     * Matched by slug and by name, because the category can be created from
     * the admin in Bangla without anyone setting an English slug. Cached for
     * an hour — categories are not created often, and this runs on every
     * homepage request.
     */
    public static function categoryIds(): array
    {
        return Cache::remember('video.category.ids', 3600, fn () => Category::query()
            ->where(function ($q) {
                $q->whereIn('slug', ['video', 'videos'])
                  ->orWhere('name', 'like', '%ভিডিও%');
            })
            ->pluck('id')
            ->all()
        );
    }

    /** True when this category is one of them — used to leave video sections alone. */
    public static function isVideoCategory(?int $categoryId): bool
    {
        return $categoryId !== null && in_array($categoryId, static::categoryIds(), true);
    }

    /**
     * Drop video stories from a post query.
     *
     * Note the NULL handling on both columns. `where('type', '!=', 'video')`
     * alone would silently drop every row whose type was never set, and
     * `whereNotIn('category_id', …)` would drop every row with no primary
     * category — between them that is most of the imported archive.
     */
    public static function exclude(Builder $query): Builder
    {
        $ids = static::categoryIds();

        return $query
            ->where(function ($q) {
                $q->whereNull('type')->orWhere('type', '!=', 'video');
            })
            ->when($ids, fn ($q) => $q
                ->where(function ($sub) use ($ids) {
                    $sub->whereNull('category_id')->orWhereNotIn('category_id', $ids);
                })
                ->whereDoesntHave('categories', fn ($c) => $c->whereIn('categories.id', $ids))
            );
    }
}
