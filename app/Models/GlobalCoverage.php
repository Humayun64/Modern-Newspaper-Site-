<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A foreign-media highlight: a headline, the outlet it came from, and the
 * document itself.
 *
 * "The document" is a PDF or an image. The current site's highlights are
 * mostly page scans saved as .webp, not PDFs at all, so insisting on PDF
 * would have meant the client could not upload the very things this section
 * exists for. The column is still called pdf_path — renaming it would cost a
 * migration for no gain.
 *
 * See the migration for why these are not posts.
 */
class GlobalCoverage extends Model
{
    /** Extensions treated as a picture rather than a document. */
    protected const IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

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

    /** Where the card links to: the uploaded file if there is one, else the outlet. */
    public function getLinkAttribute(): ?string
    {
        if (filled($this->pdf_path)) {
            return $this->fileUrl($this->pdf_path);
        }

        return filled($this->source_url) ? $this->source_url : null;
    }

    /** The uploaded document, whatever its type. */
    public function getFileUrlAttribute(): ?string
    {
        return filled($this->pdf_path) ? $this->fileUrl($this->pdf_path) : null;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return filled($this->cover_path) ? $this->fileUrl($this->cover_path) : null;
    }

    public function getHasFileAttribute(): bool
    {
        return filled($this->pdf_path);
    }

    /** A picture opens in the viewer directly; a PDF needs a frame. */
    public function getIsImageAttribute(): bool
    {
        if (blank($this->pdf_path)) {
            return false;
        }

        return in_array($this->extension(), self::IMAGE_TYPES, true);
    }

    public function getIsPdfAttribute(): bool
    {
        return filled($this->pdf_path) && $this->extension() === 'pdf';
    }

    /** Kept for older templates. */
    public function getHasPdfAttribute(): bool
    {
        return $this->is_pdf;
    }

    /**
     * What the card's corner badge says.
     *
     * A page scan is not a PDF and should not claim to be — readers who have
     * been told "PDF" and get a picture lose trust in the next badge too.
     */
    public function getBadgeAttribute(): ?string
    {
        return match (true) {
            $this->is_pdf   => 'PDF',
            $this->is_image => 'ছবি',
            default         => null,
        };
    }

    protected function extension(): string
    {
        // Query strings and fragments are common on pasted URLs.
        $path = parse_url((string) $this->pdf_path, PHP_URL_PATH) ?: (string) $this->pdf_path;

        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /** Paths may be a stored file or a pasted absolute URL. */
    protected function fileUrl(string $path): string
    {
        return Str::startsWith($path, ['http://', 'https://', '/'])
            ? $path
            : asset('storage/'.$path);
    }
}
