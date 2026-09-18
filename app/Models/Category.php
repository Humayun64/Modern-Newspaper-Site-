<?php

namespace App\Models;

use App\Support\BanglaSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'show_in_menu' => 'boolean',
            'is_active'    => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (blank($category->slug)) {
                // Prefer the English name for the slug so category URLs stay
                // readable: /category/breaking instead of /category/ব্রেকিং
                $source = filled($category->name_en) ? $category->name_en : $category->name;
                $category->slug = BanglaSlug::unique($source, static::class, $category);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

       public function allPosts()
    {
        return $this->belongsToMany(Post::class, 'post_category');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeInMenu($q)
    {
        return $q->active()->where('show_in_menu', true)->whereNull('parent_id');
    }

    public function getUrlAttribute(): string
    {
        return url('/category/' . $this->slug);
    }
}
