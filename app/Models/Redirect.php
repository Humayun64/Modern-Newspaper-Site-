<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['last_used_at' => 'datetime'];
    }

    /**
     * Normalise any URL or path into the form stored in from_path:
     * no scheme, no host, no leading or trailing slash, no query string.
     *
     * WordPress links arrive as full URLs with a trailing slash; a browser
     * may send either. Both must resolve to the same row.
     */
    public static function normalise(string $value): string
    {
        $path = parse_url(trim($value), PHP_URL_PATH) ?? $value;

        return trim(rawurldecode((string) $path), '/');
    }

    /**
     * Look up where an old path should send the visitor.
     * Returns null when there is nothing to redirect to.
     */
    public static function resolve(string $path): ?self
    {
        $path = static::normalise($path);

        if ($path === '') {
            return null;
        }

        return static::where('from_path', $path)->first();
    }

    /** Count the hit without touching updated_at or firing model events. */
    public function recordHit(): void
    {
        static::withoutEvents(function () {
            static::whereKey($this->id)->update([
                'hits'         => $this->hits + 1,
                'last_used_at' => now(),
            ]);
        });
    }

    public function getTargetUrlAttribute(): string
    {
        return url('/' . ltrim($this->to_path, '/'));
    }
}
