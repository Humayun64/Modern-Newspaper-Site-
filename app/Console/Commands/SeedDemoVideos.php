<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Three demo video articles, so "সর্বশেষ ভিডিও" is not an empty page when
 * the client first opens it.
 *
 * They exist to show that each of the three routes into the player works:
 * an ordinary YouTube link, a Shorts link, and a file played by the site
 * itself. The client replaces them with real stories and removes these.
 *
 * Every one of them carries the slug prefix `demo-video-`, which is how
 * --remove finds them again. Nothing else on the site uses that prefix, so
 * the removal cannot reach a real article even if the titles were edited.
 *
 *   php artisan site:demo-videos --dry-run    say what would be created
 *   php artisan site:demo-videos              create them
 *   php artisan site:demo-videos --remove     delete them again
 *
 * The three sources are options, because a video on someone else's channel
 * can be taken down at any time and then the demo plays nothing:
 *
 *   php artisan site:demo-videos --youtube="https://www.youtube.com/watch?v=XXXX"
 */
class SeedDemoVideos extends Command
{
    protected $signature = 'site:demo-videos
        {--remove        : Delete the demo videos instead of creating them}
        {--dry-run       : Show what would happen, change nothing}
        {--category=ভিডিও : Category they go in; created if missing}
        {--youtube=       : YouTube link for demo 1}
        {--shorts=        : YouTube Shorts link for demo 2}
        {--mp4=           : Direct .mp4 link for demo 3}';

    protected $description = 'Create (or remove) demo video articles so the video page is not empty';

    /** Anything whose slug starts with this is ours to delete. */
    protected string $prefix = 'demo-video-';

    public function handle(): int
    {
        $this->line('');

        return $this->option('remove') ? $this->remove() : $this->create();
    }

    // ------------------------------------------------------------------

    protected function create(): int
    {
        $dry = (bool) $this->option('dry-run');

        $youtube = $this->option('youtube') ?: 'https://www.youtube.com/watch?v=jNQXAC9IVRw';
        $shorts  = $this->option('shorts')  ?: 'https://www.youtube.com/shorts/jNQXAC9IVRw';
        $mp4     = $this->option('mp4')     ?: 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4';

        $category = $this->category();

        if (! $category) {
            return self::FAILURE;
        }

        $author = User::query()->orderBy('id')->first();

        if (! $author) {
            $this->error('No user on this site, so the articles would have no author.');

            return self::FAILURE;
        }

        /*
         * A thumbnail matters more than it looks: without one the article
         * shows as a grey box in every list on the homepage, and the client
         * reads that as "the video feature is broken". YouTube serves a
         * still for any video at a predictable address, and the model's
         * thumb accessor already passes a full URL straight through.
         */
        $rows = [
            [
                'slug'      => $this->prefix.'1',
                'title'     => 'ডেমো ভিডিও ১ — ইউটিউব লিংক থেকে',
                'excerpt'   => 'এটি একটি নমুনা পোস্ট। পোস্ট এডিট করে ইউটিউব লিংকটি বদলে দিলেই আসল ভিডিও চলবে।',
                'video_url' => $youtube,
                'thumb'     => $this->youtubeThumb($youtube),
                'how'       => 'ভিডিও লিংকের ঘরে সাধারণ ইউটিউব লিংক বসানো হয়েছে। সাইট নিজে থেকে সেটিকে প্লেয়ারে পরিণত করেছে।',
            ],
            [
                'slug'      => $this->prefix.'2',
                'title'     => 'ডেমো ভিডিও ২ — ইউটিউব শর্টস থেকে',
                'excerpt'   => 'শর্টস লিংকও একইভাবে কাজ করে — আলাদা কিছু করতে হয় না।',
                'video_url' => $shorts,
                'thumb'     => $this->youtubeThumb($shorts),
                'how'       => 'শর্টস, youtu.be, ফেসবুক ও ভিমিও-র লিংকও একই ঘরে বসে। ঠিকানা দেখে সাইট নিজেই বুঝে নেয়।',
            ],
            [
                'slug'       => $this->prefix.'3',
                'title'      => 'ডেমো ভিডিও ৩ — সরাসরি ভিডিও ফাইল',
                'excerpt'    => 'লিংক না থাকলে MP4 ফাইল আপলোড করা যায়। এই নমুনাটি সেভাবেই চলছে।',
                'video_file' => $mp4,
                'thumb'      => null,
                'how'        => 'এটি আপলোড করা ফাইলের পথ। অ্যাডমিনে "Or upload a video" ঘরে ফাইল দিলে ঠিক এভাবেই চলবে।',
            ],
        ];

        $made = 0;

        foreach ($rows as $row) {
            $existing = Post::withTrashed()->where('slug', $row['slug'])->first();

            if ($existing) {
                $this->line('  · '.$row['title'].' — already there');
                continue;
            }

            if ($dry) {
                $this->line('  + '.$row['title'].' — would be created');
                $made++;
                continue;
            }

            $data = [
                'title'          => $row['title'],
                'slug'           => $row['slug'],
                'excerpt'        => $row['excerpt'],
                'body'           => $this->body($row['how']),
                'category_id'    => $category->id,
                'author_id'      => $author->id,
                'status'         => 'published',
                'published_at'   => now(),
                'type'           => 'video',
                'video_url'      => $row['video_url'] ?? null,
                'video_file'     => $row['video_file'] ?? null,
                'featured_image' => $row['thumb'],
                'image_credit'   => 'ডেমো',
            ];

            // Columns differ between the local copy and the server from time
            // to time; an unknown one is dropped rather than failing the run.
            $table = (new Post)->getTable();
            $data  = array_filter(
                $data,
                fn ($key) => Schema::hasColumn($table, $key),
                ARRAY_FILTER_USE_KEY
            );

            $post = Post::create($data);
            $post->categories()->syncWithoutDetaching([$category->id]);

            $this->line('  + '.$row['title'].' — created');
            $made++;
        }

        if (! $dry) {
            $this->forgetCaches();
        }

        $this->line('');
        $this->info($dry
            ? "Dry run — {$made} demo video(s) would be created."
            : "{$made} demo video(s) created in \"{$category->name}\".");

        if (! $dry && $made > 0) {
            $this->line('');
            $this->comment('Check them at /video — and these are the sources used:');
            $this->line('  1  '.$youtube);
            $this->line('  2  '.$shorts);
            $this->line('  3  '.$mp4);
            $this->line('');
            $this->comment('A video on someone else\'s channel can be taken down. If one of these');
            $this->comment('plays nothing, open the article in the admin and paste another link —');
            $this->comment('or run this again with --youtube="…" after removing them.');
            $this->line('');
            $this->comment('Remove them all with:  php artisan site:demo-videos --remove');
        }

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------

    protected function remove(): int
    {
        $posts = Post::withTrashed()
            ->where('slug', 'like', $this->prefix.'%')
            ->get();

        if ($posts->isEmpty()) {
            $this->info('No demo videos found — nothing to remove.');

            return self::SUCCESS;
        }

        foreach ($posts as $post) {
            $this->line('  − '.$post->title);
        }

        if ($this->option('dry-run')) {
            $this->line('');
            $this->info('Dry run — nothing was deleted.');

            return self::SUCCESS;
        }

        foreach ($posts as $post) {
            $post->categories()->detach();
            $post->tags()->detach();
            $post->forceDelete();
        }

        $this->forgetCaches();

        $this->line('');
        $this->info($posts->count().' demo video(s) deleted.');
        $this->comment('The ভিডিও category is left in place — real video stories go in it.');

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------

    /** The category they go in, made if it is not there yet. */
    protected function category(): ?Category
    {
        $name = $this->normalise((string) $this->option('category'));

        $found = Category::all()->first(fn ($c) => $this->normalise($c->name) === $name);

        if ($found) {
            return $found;
        }

        if ($this->option('dry-run')) {
            $this->line("  + category \"{$name}\" — would be created");

            // A stand-in so the rest of the dry run can be printed.
            return new Category(['name' => $name]);
        }

        $data = [
            'name'      => $name,
            'slug'      => 'video',
            'is_active' => true,
        ];

        $table = (new Category)->getTable();
        $data  = array_filter(
            $data,
            fn ($key) => Schema::hasColumn($table, $key),
            ARRAY_FILTER_USE_KEY
        );

        $category = Category::create($data);

        $this->line("  + category \"{$name}\" — created");

        return $category;
    }

    /** YouTube serves a still for every video at a fixed address. */
    protected function youtubeThumb(string $url): ?string
    {
        foreach (['/[?&]v=([\w-]{6,})/', '#youtu\.be/([\w-]{6,})#', '#/shorts/([\w-]{6,})#'] as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return 'https://i.ytimg.com/vi/'.$m[1].'/hqdefault.jpg';
            }
        }

        return null;
    }

    protected function body(string $how): string
    {
        return '<p>এটি একটি <strong>নমুনা পোস্ট</strong>, শুধু দেখানোর জন্য যে ভিডিও ঠিকভাবে কাজ করছে। '
            .'আসল খবর দেওয়ার সময় এটি মুছে ফেলুন।</p>'
            .'<p>'.e($how).'</p>'
            .'<p>অ্যাডমিন প্যানেলে পোস্ট এডিট করলে ডান পাশে <em>Featured image and video</em> অংশে '
            .'দুটি ঘর পাবেন — একটিতে ভিডিওর লিংক, অন্যটিতে ফাইল আপলোড। যেকোনো একটি দিলেই হবে; '
            .'দুটি দিলে আপলোড করা ফাইলটি চলবে।</p>';
    }

    protected function forgetCaches(): void
    {
        foreach (['home.sections', 'footer.recent', 'widget.tags', 'home.latest_video'] as $key) {
            Cache::forget($key);
        }
    }

    protected function normalise(?string $text): string
    {
        $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', (string) $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
