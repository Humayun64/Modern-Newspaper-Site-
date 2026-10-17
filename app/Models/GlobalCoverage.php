<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A foreign-media highlight: a headline, the outlet it came from, and a PDF.
 *
 * See the migration for why these are not posts.
 */
class GlobalCoverage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active'    => 'boolean',
            'published_on' => 'date',
        ];
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)
                 ->orderBy('sort_order')
                 ->orderByDesc('published_on')
                 ->orderByDesc('id');
    }

    /** Where the card links to: the PDF if there is one, else the outlet. */
    public function getLinkAttribute(): ?string
    {
        if (filled($this->pdf_path)) {
            return $this->fileUrl($this->pdf_path);
        }

        return filled($this->source_url) ? $this->source_url : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return filled($this->cover_path) ? $this->fileUrl($this->cover_path) : null;
    }

    public function getHasPdfAttribute(): bool
    {
        return filled($this->pdf_path);
    }

    /** Paths may be a stored file or a pasted absolute URL. */
    protected function fileUrl(string $path): string
    {
        return Str::startsWith($path, ['http://', 'https://', '/'])
            ? $path
            : asset('storage/'.$path);
    }
}
