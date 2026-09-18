<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Tag;
use App\Models\User;
use App\Support\BanglaSlug;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportWordPress extends Command
{
    protected $signature = 'import:wordpress
        {--url= : The WordPress site, e.g. https://amardesh24.news}
        {--limit=0 : Stop after this many posts (0 = all)}
        {--per-page=20 : Posts fetched per request}
        {--page=1 : Page to start from, for resuming}
        {--skip-images : Do not download any images}
        {--skip-inline : Download featured images but leave in-article images pointing at the old site}
        {--dry-run : Report what would happen and write nothing}';

    protected $description = 'Import posts, categories, tags and images from a WordPress site over its REST API';

    protected string $base;
    protected bool $dry;

    /** WordPress term id => local model id */
    protected array $categoryMap = [];
    protected array $tagMap = [];
    protected array $authorMap = [];

    protected array $stats = [
        'created' => 0, 'updated' => 0, 'skipped' => 0,
        'images'  => 0, 'imageFailed' => 0, 'redirects' => 0,
    ];

    public function handle(): int
    {
        $this->base = rtrim((string) ($this->option('url') ?: config('app.url')), '/');
        $this->dry  = (bool) $this->option('dry-run');

        if (! Str::startsWith($this->base, ['http://', 'https://'])) {
            $this->error('Pass a full site address, for example --url=https://amardesh24.news');
            return self::FAILURE;
        }

        $this->line('');
        $this->info('Source: ' . $this->base);

        if ($this->dry) {
            $this->warn('Dry run — nothing will be written.');
        }

        // Fail here rather than halfway through a 3,000-post import.
        if (! $this->checkApi()) {
            return self::FAILURE;
        }

        $this->importCategories();
        $this->importTags();
        $this->importPosts();

        $this->summary();

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------

    protected function checkApi(): bool
    {
        $this->line('Checking the REST API…');

        try {
            $response = $this->get('/wp-json/wp/v2/posts', ['per_page' => 1]);
        } catch (\Throwable $e) {
            $this->error('Could not reach the site: ' . $e->getMessage());
            return false;
        }

        if ($response->failed()) {
            $this->error('The REST API returned HTTP ' . $response->status() . '.');
            $this->line('Open ' . $this->base . '/wp-json/wp/v2/posts in a browser to check it is public.');
            $this->line('Some security plugins disable it; the import needs it switched on.');
            return false;
        }

        $total = (int) $response->header('X-WP-Total');
        $this->info('Connected. ' . number_format($total) . ' posts available.');

        return true;
    }

    protected function get(string $path, array $query = [])
    {
        return Http::timeout(45)
            ->retry(3, 2000, throw: false)
            ->acceptJson()
            ->get($this->base . $path, $query);
    }

    // ------------------------------------------------------------------

    protected function importCategories(): void
    {
        $this->line('');
        $this->line('Categories…');

        foreach ($this->allTerms('categories') as $term) {
            $name = $this->decode($term['name'] ?? '');

            if ($name === '' || Str::lower($name) === 'uncategorized') {
                continue;
            }

            $existing = Category::where('wp_term_id', $term['id'])
                ->orWhere('slug', $term['slug'])
                ->first();

            if ($existing) {
                $this->categoryMap[$term['id']] = $existing->id;

                if (! $this->dry && blank($existing->wp_term_id)) {
                    $existing->update(['wp_term_id' => $term['id']]);
                }
                continue;
            }

            if ($this->dry) {
                $this->line('  would create: ' . $name);
                continue;
            }

            $category = Category::create([
                'name'        => $name,
                'slug'        => $term['slug'] ?: BanglaSlug::make($name),
                'description' => $this->decode($term['description'] ?? ''),
                'wp_term_id'  => $term['id'],
                'is_active'   => true,
                // New sections stay out of the navigation until you decide
                // they belong there.
                'show_in_menu' => false,
            ]);

            $this->categoryMap[$term['id']] = $category->id;
            $this->line('  created: ' . $name);
        }
    }

    protected function importTags(): void
    {
        $this->line('Tags…');

        foreach ($this->allTerms('tags') as $term) {
            $name = $this->decode($term['name'] ?? '');

            if ($name === '') {
                continue;
            }

            $existing = Tag::where('wp_term_id', $term['id'])->orWhere('slug', $term['slug'])->first();

            if ($existing) {
                $this->tagMap[$term['id']] = $existing->id;
                continue;
            }

            if ($this->dry) {
                continue;
            }

            $tag = Tag::create([
                'name'       => $name,
                'slug'       => $term['slug'] ?: BanglaSlug::make($name),
                'wp_term_id' => $term['id'],
            ]);

            $this->tagMap[$term['id']] = $tag->id;
        }

        $this->line('  ' . count($this->tagMap) . ' tags mapped.');
    }

    protected function allTerms(string $type): array
    {
        $out  = [];
        $page = 1;

        do {
            $response = $this->get("/wp-json/wp/v2/{$type}", ['per_page' => 100, 'page' => $page]);

            if ($response->failed()) {
                break;
            }

            $batch = $response->json() ?: [];
            $out   = array_merge($out, $batch);
            $page++;
        } while (count($batch) === 100 && $page <= 50);

        return $out;
    }

    // ------------------------------------------------------------------

    protected function importPosts(): void
    {
        $perPage = max(1, (int) $this->option('per-page'));
        $limit   = (int) $this->option('limit');
        $page    = max(1, (int) $this->option('page'));
        $done    = 0;

        $this->line('');
        $this->line('Posts…');

        while (true) {
            $response = $this->get('/wp-json/wp/v2/posts', [
                'per_page' => $perPage,
                'page'     => $page,
                '_embed'   => 1,
            ]);

            if ($response->failed()) {
                // WordPress answers 400 once you page past the end.
                break;
            }

            $batch = $response->json() ?: [];

            if (empty($batch)) {
                break;
            }

            foreach ($batch as $wpPost) {
                $this->importPost($wpPost);
                $done++;

                if ($limit > 0 && $done >= $limit) {
                    $this->line('');
                    $this->comment("Stopped at the --limit of {$limit}.");
                    return;
                }
            }

            $this->line("  page {$page} done ({$done} posts so far)");
            $page++;
        }
    }

    protected function importPost(array $wp): void
    {
        $title = trim($this->decode($wp['title']['rendered'] ?? ''));

        if ($title === '') {
            $this->stats['skipped']++;
            return;
        }

        $existing = Post::withTrashed()->where('wp_id', $wp['id'])->first();

        if ($this->dry) {
            $this->line('  ' . ($existing ? 'would update: ' : 'would create: ') . Str::limit($title, 70));
            $this->stats[$existing ? 'updated' : 'created']++;
            return;
        }

        $body = $this->cleanBody($wp['content']['rendered'] ?? '');

        if (! $this->option('skip-images') && ! $this->option('skip-inline')) {
            $body = $this->localiseInlineImages($body);
        }

        $featured = null;

        if (! $this->option('skip-images')) {
            $featured = $this->downloadFeatured($wp);
        }

        $categoryIds = collect($wp['categories'] ?? [])
            ->map(fn ($id) => $this->categoryMap[$id] ?? null)
            ->filter()->values();

        $tagIds = collect($wp['tags'] ?? [])
            ->map(fn ($id) => $this->tagMap[$id] ?? null)
            ->filter()->values();

        $attributes = [
            'wp_id'            => $wp['id'],
            'title'            => $title,
            'body'             => $body,
            'excerpt'          => trim(strip_tags($this->decode($wp['excerpt']['rendered'] ?? ''))) ?: null,
            'category_id'      => $categoryIds->first(),
            'author_id'        => $this->resolveAuthor($wp),
            'status'           => ($wp['status'] ?? 'publish') === 'publish' ? 'published' : 'draft',
            'published_at'     => isset($wp['date_gmt']) ? \Carbon\Carbon::parse($wp['date_gmt'], 'UTC') : now(),
            'legacy_url'       => $wp['link'] ?? null,
            'meta_description' => null,
        ];

        if ($featured) {
            $attributes['featured_image'] = $featured;
        }

        if ($existing) {
            // Keep the slug the site already publishes under — changing it
            // would break links that are already indexed.
            $existing->fill($attributes)->save();
            $post = $existing;
            $this->stats['updated']++;
        } else {
            $attributes['slug'] = BanglaSlug::unique(
                $wp['slug'] ? rawurldecode($wp['slug']) : $title,
                Post::class
            );

            $post = Post::create($attributes);
            $this->stats['created']++;
        }

        if ($categoryIds->isNotEmpty()) {
            $post->categories()->sync($categoryIds);
        }

        if ($tagIds->isNotEmpty()) {
            $post->tags()->sync($tagIds);
        }

        $this->recordRedirect($wp['link'] ?? null, $post);
    }

    // ------------------------------------------------------------------

    /**
     * Every old URL gets a 301 to the new one, so nothing that Google has
     * already indexed turns into a 404 on the day the domain switches over.
     */
    protected function recordRedirect(?string $legacyUrl, Post $post): void
    {
        if (blank($legacyUrl)) {
            return;
        }

        $from = Redirect::normalise($legacyUrl);
        $to   = $post->slug;

        if ($from === '' || $from === $to) {
            return;   // already the same address; nothing to redirect
        }

        Redirect::updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status' => 301, 'source' => 'import']
        );

        $this->stats['redirects']++;
    }

    protected function resolveAuthor(array $wp): ?int
    {
        $wpAuthorId = $wp['author'] ?? 0;

        if (isset($this->authorMap[$wpAuthorId])) {
            return $this->authorMap[$wpAuthorId];
        }

        $name = $wp['_embedded']['author'][0]['name'] ?? null;

        if (blank($name)) {
            return $this->authorMap[$wpAuthorId] = User::where('role', 'admin')->value('id');
        }

        $user = User::where('name', $name)->first();

        if (! $user) {
            // Imported writers arrive disabled with no password: their bylines
            // are preserved, but nobody gains a working account by accident.
            $user = User::create([
                'name'      => $name,
                'email'     => Str::slug($name) . '@imported.local',
                'password'  => Str::random(40),
                'role'      => 'author',
                'is_active' => false,
            ]);
        }

        return $this->authorMap[$wpAuthorId] = $user->id;
    }

    // ------------------------------------------------------------------

    protected function cleanBody(string $html): string
    {
        $html = $this->decode($html);

        // Gutenberg leaves block markers in the saved HTML.
        $html = preg_replace('/<!--\s*\/?wp:.*?-->/s', '', $html);

        // Shortcodes no plugin will be around to render.
        $html = preg_replace('/\[\/?(?:caption|gallery|embed|vc_[^\]]*|et_pb_[^\]]*)[^\]]*\]/i', '', $html);

        // WordPress emits these around lone images and empty paragraphs.
        $html = preg_replace('/<p>\s*(<img[^>]+>)\s*<\/p>/i', '$1', $html);
        $html = preg_replace('/<p>(\s|&nbsp;)*<\/p>/i', '', $html);

        return trim($html);
    }

    /** Pull in-article images onto this site so they survive the switchover. */
    protected function localiseInlineImages(string $html): string
    {
        if (! preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            return $html;
        }

        foreach (array_unique($matches[1]) as $src) {
            if (! Str::startsWith($src, $this->base)) {
                continue;   // already elsewhere; leave it alone
            }

            $stored = $this->download($src);

            if ($stored) {
                $html = str_replace($src, asset('storage/' . $stored), $html);
            }
        }

        return $html;
    }

    protected function downloadFeatured(array $wp): ?string
    {
        $url = $wp['_embedded']['wp:featuredmedia'][0]['source_url'] ?? null;

        return $url ? $this->download($url) : null;
    }

    protected function download(string $url): ?string
    {
        try {
            $name = Str::of(basename(parse_url($url, PHP_URL_PATH) ?? ''))
                ->replace(' ', '-')
                ->limit(100, '');

            if ($name->isEmpty()) {
                return null;
            }

            $path = 'imported/' . date('Y/m') . '/' . $name;

            // Already pulled in on an earlier run.
            if (Storage::disk('public')->exists($path)) {
                return $path;
            }

            $response = Http::timeout(60)->get($url);

            if ($response->failed()) {
                $this->stats['imageFailed']++;
                return null;
            }

            Storage::disk('public')->put($path, $response->body());
            $this->stats['images']++;

            return $path;
        } catch (\Throwable $e) {
            $this->stats['imageFailed']++;
            return null;
        }
    }

    protected function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ------------------------------------------------------------------

    protected function summary(): void
    {
        $this->line('');
        $this->info('Done.');
        $this->table(
            ['', 'Count'],
            [
                ['Articles created',  number_format($this->stats['created'])],
                ['Articles updated',  number_format($this->stats['updated'])],
                ['Articles skipped',  number_format($this->stats['skipped'])],
                ['Images downloaded', number_format($this->stats['images'])],
                ['Images failed',     number_format($this->stats['imageFailed'])],
                ['Redirects written', number_format($this->stats['redirects'])],
            ]
        );

        if ($this->stats['imageFailed'] > 0) {
            $this->warn('Some images could not be fetched. Re-running the command retries only the missing ones.');
        }
    }
}
