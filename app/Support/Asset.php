<?php

namespace App\Support;

/**
 * Cache-busted asset URLs.
 *
 * Browsers and Hostinger's CDN hold on to CSS for a long time, so a deploy
 * can leave visitors on the previous stylesheet while the HTML is already the
 * new one — which looks like a broken site rather than a stale cache.
 *
 * Appending the file's modification time means the URL changes whenever the
 * file does, and never otherwise. No build step, no manual version numbers.
 */
class Asset
{
    public static function versioned(string $path): string
    {
        $url  = asset($path);
        $full = public_path($path);

        if (! is_file($full)) {
            return $url;
        }

        return $url . '?v=' . filemtime($full);
    }
}
