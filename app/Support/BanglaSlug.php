<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Laravel's Str::slug() strips Bangla characters completely, so
 * "হামের উপসর্গে আরও ৩ মৃত্যু" becomes an empty string and every post
 * ends up fighting over the same slug. This helper keeps Bangla letters,
 * digits and Latin characters, and drops everything else.
 */
class BanglaSlug
{
    public static function make(string $text): string
    {
        $text = trim($text);

        // Normalise quotes and dashes that Bangla headlines use a lot.
        $text = str_replace(
            ['‘', '’', '“', '”', '–', '—', '/', '\\', ':', '|'],
            ['', '', '', '', '-', '-', '-', '-', '', '-'],
            $text
        );

        // Keep: Bangla block (u0980-u09FF), a-z, 0-9, space and hyphen.
        $text = preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s\-]/u', '', $text);

        // Collapse whitespace and hyphens.
        $text = preg_replace('/[\s\-]+/u', '-', $text);
        $text = trim($text, '-');

        // Bangla is multi-byte, so limit by characters, not bytes.
        $text = Str::limit($text, 150, '');
        $text = trim($text, '-');

        return $text !== '' ? mb_strtolower($text, 'UTF-8') : '';
    }

    /**
     * Make a slug that does not collide with an existing row.
     * Pass the model being saved so an edit does not clash with itself.
     */
    public static function unique(string $text, string $modelClass, ?Model $ignore = null): string
    {
        $base = static::make($text);

        if ($base === '') {
            $base = 'post-' . now()->format('YmdHis');
        }

        $slug = $base;
        $i = 2;

        while (static::exists($slug, $modelClass, $ignore)) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    protected static function exists(string $slug, string $modelClass, ?Model $ignore): bool
    {
        $query = $modelClass::where('slug', $slug);

        if ($ignore && $ignore->exists) {
            $query->whereKeyNot($ignore->getKey());
        }

        // Include soft-deleted rows so a restored post cannot duplicate a slug.
        if (method_exists($modelClass, 'bootSoftDeletes')) {
            $query->withTrashed();
        }

        return $query->exists();
    }
}