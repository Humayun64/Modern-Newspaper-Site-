<?php

namespace App\Http\Controllers;

use App\Models\HomeSection;
use App\Models\Post;

class HomeController extends Controller
{
    /** Layouts that belong in the top three-column row. */
    protected array $heroLayouts = ['main_news', 'editors_pick', 'latest_popular'];

    /** Layouts that belong in the right sidebar. */
    protected array $sidebarLayouts = ['trending'];

    public function __invoke()
    {
        /*
         * No cache around these queries on purpose.
         *
         * Caching Eloquent collections in the database cache store serialises
         * the models, and they can come back as incomplete objects. Every query
         * here is indexed and eager-loaded, so the homepage is a handful of
         * cheap queries. When real traffic arrives the right answer is caching
         * the rendered HTML, not the models.
         */
        $sections = HomeSection::active()->with('category')->get()
            ->map(fn (HomeSection $s) => [
                'layout'   => $s->layout,
                'title'    => $s->title,
                'category' => $s->category,
                'posts'    => $s->resolvePosts(),
            ])
            ->filter(fn ($s) => $s['posts']->isNotEmpty())
            ->values();

        return view('home', [
            'hero'    => $sections->whereIn('layout', $this->heroLayouts),
            'sidebar' => $sections->whereIn('layout', $this->sidebarLayouts),
            'main'    => $sections->reject(fn ($s) => in_array(
                $s['layout'],
                array_merge($this->heroLayouts, $this->sidebarLayouts),
                true
            )),
            'popular' => Post::published()->forCards()->popular()->limit(4)->get(),
        ]);
    }
}
