<?php

namespace App\Models;

use App\Support\BanglaSlug;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['show_in_footer' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Page $page) {
            if (blank($page->slug)) {
                $page->slug = BanglaSlug::unique($page->title, static::class, $page);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function getUrlAttribute(): string
    {
        return route('page.show', $this->slug);
    }
}
