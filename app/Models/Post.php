<?php

namespace App\Models;

use App\Support\BanglaSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'published_at'    => 'datetime',
            'is_breaking'     => 'boolean',
            'is_featured'     => 'boolean',
            'is_editors_pick' => 'boolean',
            'is_trending'     => 'boolean',
            'is_sponsored'    => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (blank($post->slug)) {
                $post->slug = BanglaSlug::unique($post->title, static::class, $post);
            }

            if (blank($post->excerpt) && filled($post->body)) {
                $post->excerpt = Str::limit(trim(strip_tags($post->body)), 200);
            }

            if (filled($post->body)) {
                // ~180 Bangla words per minute is a reasonable reading speed.
                $words = count(preg_split('/\s+/u', trim(strip_tags($post->body)), -1, PREG_SPLIT_NO_EMPTY));
                $post->reading_time = max(1, (int) ceil($words / 180));
            }

            if ($post->status === 'published' && blank($post->published_at)) {
                $post->published_at = now();
            }
        });

        /*
         * Audit trail. Only logged when somebody is signed in, so seeders and
         * the importer do not fill the log with machine noise.
         */
        static::created(function (Post $post) {
            if (Auth::check()) {
                ActivityLog::record(
                    $post->status === 'published' ? 'post_published' : 'post_created',
                    ['subject_type' => 'post', 'subject_id' => $post->id, 'description' => $post->title]
                );
            }
        });

        static::updated(function (Post $post) {
            if (! Auth::check()) {
                return;
            }

            // Going from anything to published is the moment worth recording.
            $justPublished = $post->wasChanged('status') && $post->status === 'published';

            ActivityLog::record(
                $justPublished ? 'post_published' : 'post_updated',
                ['subject_type' => 'post', 'subject_id' => $post->id, 'description' => $post->title]
            );
        });

        static::deleted(function (Post $post) {
            if (Auth::check()) {
                ActivityLog::record('post_deleted', [
                    'subject_type' => 'post', 'subject_id' => $post->id, 'description' => $post->title,
                ]);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // ---------------- relations ----------------

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'post_category');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ---------------- scopes ----------------

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')
                 ->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
    }

    public function scopeLatestFirst(Builder $q): Builder
    {
        return $q->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Always use this for listings. Without it every widget on the
     * homepage fires one extra query per post for its category and author.
     */
    public function scopeForCards(Builder $q): Builder
    {
        return $q->with(['category:id,name,slug,color', 'author:id,name,name_bn,slug'])
                 ->select([
                     'id', 'title', 'slug', 'excerpt', 'featured_image', 'type',
                     'category_id', 'author_id', 'published_at', 'views', 'reading_time',
                 ]);
    }

    public function scopeBreaking(Builder $q): Builder
    {
        return $q->published()->where('is_breaking', true)->latestFirst();
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->published()->where('is_featured', true)->latestFirst();
    }

    public function scopeEditorsPick(Builder $q): Builder
    {
        return $q->published()->where('is_editors_pick', true)->latestFirst();
    }

    public function scopeTrending(Builder $q): Builder
    {
        return $q->published()->where('is_trending', true)
                 ->orderByRaw('trending_position IS NULL, trending_position');
    }

    public function scopePopular(Builder $q, int $days = 7): Builder
    {
        return $q->published()
                 ->where('published_at', '>=', now()->subDays($days))
                 ->orderByDesc('views');
    }

    public function scopeInCategory(Builder $q, Category $category): Builder
    {
        return $q->where(function (Builder $sub) use ($category) {
            $sub->where('category_id', $category->id)
                ->orWhereHas('categories', fn (Builder $c) => $c->where('categories.id', $category->id));
        });
    }

    // ---------------- helpers ----------------

    public function getUrlAttribute(): string
    {
        return url('/' . $this->slug);
    }

    public function getThumbAttribute(): string
    {
        if (blank($this->featured_image)) {
            return asset('images/placeholder.jpg');
        }

        // Demo posts store a full URL; real uploads store a storage path.
        return Str::startsWith($this->featured_image, ['http://', 'https://', '/'])
            ? $this->featured_image
            : asset('storage/' . $this->featured_image);
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
