<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;

/**
 * The "সাম্প্রতিক" and "Latest Video" destinations in the header.
 *
 * These are cross-category listings — a reader asking for the latest video
 * does not care which section it was filed under — so they cannot be served
 * by CategoryController. They reuse the archive view, which means the cards,
 * pagination and empty state behave exactly as a category page does.
 */
class LatestController extends Controller
{
    /**
     * Category names that mean "this is video", whatever the article's own
     * type field says.
     *
     * Everything here came in from WordPress, where the post type was always
     * "post" — nothing carries type = video. Matching on these as well is
     * what makes the button lead somewhere rather than to an empty page.
     */
    protected array $videoCategories = ['ভিডিও', 'video', 'ভিডিও সংবাদ'];

    protected array $podcastCategories = ['পডকাস্ট', 'podcast'];

    /** Everything, newest first. */
    public function latest()
    {
        return $this->listing(
            heading:   'সাম্প্রতিক',
            intro:     'সব বিভাগের সর্বশেষ প্রকাশিত খবর।',
            filter:    null,
            emptyText: 'এখনও কোনো খবর প্রকাশ করা হয়নি।',
        );
    }

    /** Anything that is a video: by type, by having a video link, or by category. */
    public function video()
    {
        $categoryIds = $this->categoryIds([...$this->videoCategories, ...$this->podcastCategories]);

        return $this->listing(
            heading:   'সর্বশেষ ভিডিও',
            intro:     'সর্বশেষ প্রকাশিত ভিডিও ও পডকাস্ট।',
            filter:    fn (Builder $q) => $this->mediaFilter($q, ['video', 'podcast'], $categoryIds, true),
            emptyText: 'এখনও কোনো ভিডিও প্রকাশ করা হয়নি।',
        );
    }

    /** Podcast episodes. */
    public function podcast()
    {
        $categoryIds = $this->categoryIds($this->podcastCategories);

        return $this->listing(
            heading:   'পডকাস্ট',
            intro:     'সর্বশেষ প্রকাশিত পডকাস্ট পর্ব।',
            filter:    fn (Builder $q) => $this->mediaFilter($q, ['podcast'], $categoryIds, false),
            emptyText: 'এখনও কোনো পডকাস্ট প্রকাশ করা হয়নি।',
        );
    }

    /**
     * An article counts as video if ANY of these is true: its type says so,
     * it has a video link on it, or it sits in a video/podcast category.
     *
     * $withVideoUrl is off for podcasts — an article with a YouTube link is
     * not an episode of a podcast.
     */
    protected function mediaFilter(Builder $q, array $types, array $categoryIds, bool $withVideoUrl): Builder
    {
        return $q->where(function (Builder $sub) use ($types, $categoryIds, $withVideoUrl) {
            $sub->whereIn('type', $types);

            if ($withVideoUrl) {
                // Either a pasted link or an uploaded file makes it a video.
                $sub->orWhere(fn (Builder $v) => $v->whereNotNull('video_url')->where('video_url', '!=', ''))
                    ->orWhere(fn (Builder $v) => $v->whereNotNull('video_file')->where('video_file', '!=', ''));
            }

            if ($categoryIds !== []) {
                $sub->orWhereIn('category_id', $categoryIds)
                    ->orWhereHas('categories', fn (Builder $c) => $c->whereIn('categories.id', $categoryIds));
            }
        });
    }

    /** Category ids for a list of names, ignoring stray spacing. */
    protected function categoryIds(array $names): array
    {
        $wanted = array_map(fn ($n) => $this->normalise($n), $names);

        return Category::query()
            ->get(['id', 'name', 'slug'])
            ->filter(fn ($c) => in_array($this->normalise($c->name), $wanted, true)
                             || in_array($this->normalise($c->slug), $wanted, true))
            ->pluck('id')
            ->all();
    }

    protected function normalise(?string $text): string
    {
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $text);

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
    }

    /** Shared body. A null filter means "any article". */
    protected function listing(string $heading, string $intro, ?\Closure $filter, string $emptyText)
    {
        $posts = Post::published()
            ->forCards()
            ->when($filter !== null, $filter)
            ->latestFirst()
            ->paginate(12)
            ->withQueryString();

        return view('pages.archive', [
            'heading'     => $heading,
            'description' => $intro,
            'posts'       => $posts,
            'emptyText'   => $emptyText,
        ]);
    }
}
