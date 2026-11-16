<?php

namespace App\Models;

use App\Support\VideoContent;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Resolve this section's posts based on its source setting.
     */
    public function resolvePosts()
    {
        $query = Post::query()->published()->forCards();

        /*
         * Video stories stay out of the news listings — they have their own
         * page. The one exception is a section pointed AT a video category:
         * there the videos are the whole point, and filtering them would
         * leave an empty block.
         */
        if (! VideoContent::isVideoCategory($this->category_id)) {
            $query = VideoContent::exclude($query);
        }

        $query = match ($this->source) {
            'featured'     => $query->where('is_featured', true)->latestFirst(),
            'editors_pick' => $query->where('is_editors_pick', true)->latestFirst(),
            'trending'     => $query->where('is_trending', true)
                                    ->orderByRaw('trending_position IS NULL, trending_position'),
            'popular'      => $query->where('published_at', '>=', now()->subDays(7))
                                    ->orderByDesc('views'),
            // Falls back to latest when no category has been chosen yet,
            // so a half-configured section still renders instead of vanishing.
            'category'     => $this->category_id
                                    ? $query->where('category_id', $this->category_id)->latestFirst()
                                    : $query->latestFirst(),
            default        => $query->latestFirst(),
        };

        return $query->limit($this->limit)->get();
    }
}
