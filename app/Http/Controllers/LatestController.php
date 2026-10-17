<?php

namespace App\Http\Controllers;

use App\Models\Post;

/**
 * The "সাম্প্রতিক" and "Latest Video" destinations added to the header.
 *
 * These are cross-category listings — a reader asking for the latest video
 * does not care which section it was filed under — so they cannot be served
 * by CategoryController. They reuse the same archive view, which means the
 * cards, pagination and empty state all behave exactly as a category page.
 */
class LatestController extends Controller
{
    /** Everything, newest first. */
    public function latest()
    {
        return $this->listing(
            heading:   'সাম্প্রতিক',
            intro:     'সব বিভাগের সর্বশেষ প্রকাশিত খবর।',
            types:     [],
            emptyText: 'এখনও কোনো খবর প্রকাশ করা হয়নি।',
        );
    }

    /** Video posts only. */
    public function video()
    {
        return $this->listing(
            heading:   'সর্বশেষ ভিডিও',
            intro:     'সর্বশেষ প্রকাশিত ভিডিও প্রতিবেদন।',
            types:     ['video'],
            emptyText: 'এখনও কোনো ভিডিও প্রকাশ করা হয়নি।',
        );
    }

    /** Podcast posts only. */
    public function podcast()
    {
        return $this->listing(
            heading:   'পডকাস্ট',
            intro:     'সর্বশেষ প্রকাশিত পডকাস্ট পর্ব।',
            types:     ['podcast'],
            emptyText: 'এখনও কোনো পডকাস্ট প্রকাশ করা হয়নি।',
        );
    }

    /**
     * Shared body. $types empty means "any type"; otherwise the listing is
     * narrowed to those post types.
     */
    protected function listing(string $heading, string $intro, array $types, string $emptyText)
    {
        $posts = Post::published()
            ->forCards()
            ->when($types !== [], fn ($q) => $q->whereIn('type', $types))
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
