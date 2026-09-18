<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class FeedController extends Controller
{
    /** Google's limit is 50,000 URLs per sitemap; 1,000 keeps files small and fast. */
    protected const PER_SITEMAP = 1000;

    /** Sitemap index — the one address you give Google Search Console. */
    public function sitemapIndex()
    {
        $pages = (int) ceil(max(1, Post::published()->count()) / self::PER_SITEMAP);

        $maps = [route('sitemap.categories'), route('sitemap.pages')];

        for ($i = 1; $i <= $pages; $i++) {
            $maps[] = route('sitemap.posts', $i);
        }

        // Google News only reads the last 48 hours, so it is its own file.
        $maps[] = route('sitemap.news');

        return response()
            ->view('xml.sitemap-index', ['maps' => $maps, 'updated' => now()])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function sitemapPosts(int $page = 1)
    {
        $posts = Post::published()
            ->select('slug', 'updated_at', 'published_at', 'featured_image', 'title')
            ->latestFirst()
            ->forPage(max(1, $page), self::PER_SITEMAP)
            ->get();

        abort_if($posts->isEmpty() && $page > 1, 404);

        $urls = $posts->map(fn (Post $p) => [
            'loc'        => route('post.show', $p->slug),
            'lastmod'    => ($p->updated_at ?? $p->published_at)?->toAtomString(),
            'changefreq' => 'weekly',
            'priority'   => '0.8',
            'image'      => $p->featured_image ? $p->thumb : null,
            'title'      => $p->title,
        ]);

        return $this->xml('xml.sitemap', ['urls' => $urls]);
    }

    public function sitemapCategories()
    {
        $urls = Category::active()->get()->map(fn (Category $c) => [
            'loc'        => route('category.show', $c->slug),
            'lastmod'    => $c->updated_at?->toAtomString(),
            'changefreq' => 'daily',
            'priority'   => '0.7',
        ]);

        $urls->prepend([
            'loc'        => route('home'),
            'lastmod'    => now()->toAtomString(),
            'changefreq' => 'hourly',
            'priority'   => '1.0',
        ]);

        return $this->xml('xml.sitemap', ['urls' => $urls]);
    }

    public function sitemapPages()
    {
        $urls = Page::published()->get()->map(fn (Page $p) => [
            'loc'        => route('page.show', $p->slug),
            'lastmod'    => $p->updated_at?->toAtomString(),
            'changefreq' => 'monthly',
            'priority'   => '0.4',
        ]);

        return $this->xml('xml.sitemap', ['urls' => $urls]);
    }

    /**
     * Google News sitemap. Only articles from the last 48 hours belong here —
     * older entries are ignored and a sitemap full of them looks like spam.
     */
    public function sitemapNews()
    {
        $posts = Cache::remember('sitemap.news', 300, fn () =>
            Post::published()
                ->with('category:id,name')
                ->where('published_at', '>=', now()->subHours(48))
                ->latestFirst()
                ->limit(1000)
                ->get(['id', 'title', 'slug', 'published_at', 'category_id'])
        );

        return $this->xml('xml.sitemap-news', [
            'posts'    => $posts,
            'siteName' => Setting::get('site_name', 'amarDesh24.news'),
        ]);
    }

    /** RSS, optionally narrowed to one section. */
    public function rss(?string $categorySlug = null)
    {
        $category = $categorySlug
            ? Category::where('slug', $categorySlug)->firstOrFail()
            : null;

        $posts = Post::published()
            ->forCards()
            ->when($category, fn ($q) => $q->inCategory($category))
            ->latestFirst()
            ->limit(30)
            ->get();

        $name = Setting::get('site_name', 'amarDesh24.news');

        return $this->xml('xml.rss', [
            'posts'       => $posts,
            'title'       => $category ? "{$name} — {$category->name}" : $name,
            'description' => Setting::get('meta_description') ?: Setting::get('site_tagline', ''),
            'link'        => $category ? route('category.show', $category->slug) : route('home'),
            'self'        => url()->current(),
        ]);
    }

    public function robots()
    {
        // Driven by APP_INDEXABLE, not APP_ENV: a staging copy runs in
        // production mode but must stay out of Google entirely.
        if (! config('site.indexable')) {
            return response("User-agent: *\nDisallow: /")
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /search',
            'Allow: /',
            '',
            'Sitemap: ' . route('sitemap.index'),
        ];

        return response(implode("\n", $lines))
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function xml(string $view, array $data)
    {
        return response()
            ->view($view, $data)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
